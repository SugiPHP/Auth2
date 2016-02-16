<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2;

use SugiPHP\Auth2\Gateway\PasswordGatewayInterface;
use SugiPHP\Auth2\Token\TokenInterface;
use SugiPHP\Auth2\Validator\ValidatorInterface;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\Exception\InvalidArgumentException;
use SugiPHP\Auth2\Exception\InvalidTokenException;
use SugiPHP\Auth2\Exception\UserBlockedException;
use SugiPHP\Auth2\User\UserInterface;
use SugiPHP\Auth2\User\User;
use SugiPHP\Auth2\LoggerTrait;
use SugiPHP\Auth2\StorageTrait;
use UnexpectedValueException;

class PasswordService extends Login
{
    // use PasswordHashTrait;
    // use LoggerTrait;
    // use StorageTrait;

    /**
     * @var Instance of PasswordGatewayInterface
     */
    private $gateway;

    /**
     * @var Instance of ValidatorInterface
     */
    private $validator;

    /**
     * @var Instance of TokenInterface
     */
    private $tokenGen;

    public function __construct(PasswordGatewayInterface $gateway, TokenInterface $tokenGen, ValidatorInterface $validator)
    {
        parent::__construct($gateway);
        $this->gateway = $gateway;
        $this->tokenGen = $tokenGen;
        $this->validator = $validator;
    }

    public function genToken($email)
    {
        // check email is set
        if (!$email) {
            // exception code 1 for the 1st argument
            throw new InvalidArgumentException("Моля въведете email адрес", 1);
        }

        if (!$user = $this->gateway->getByEmail($email)) {
            throw new GeneralException("Несъществуващ потребител");
        }
        // check the state of the user
        $this->checkState($user);
        // generate new token
        $token = $this->tokenGen->generateToken($user);

        // return a new user instance with generated token
        return $user->withToken($token);
    }

    public function resetPassword($token, $password1, $password2)
    {
        // checks password is set
        if (!$password1) {
            throw new InvalidArgumentException("Моля въведете парола", 2);
        }

        // checks password is set
        if (!$password2) {
            throw new InvalidArgumentException("Моля въведете паролата отново", 3);
        }

        // check token is given
        if (!$token) {
            $this->log("error", "Cannot reset user password: Missing token parameter");
            throw new InvalidTokenException("Missing token", 1);
        }

        // Check for password strength and throw InvalidArgumentException on error
        $this->validator->checkPassword($password1);
        // Check passwords match and throw InvalidArgumentException on error
        $this->validator->checkPasswordConfirmation($password1, $password2);

        if (!$userId = $this->tokenGen->fetchToken($token)) {
            $this->log("error", "Cannot reset user password: Token provided is invalid: {$token}");
            throw new InvalidTokenException("Invalid token");
        }

        if (!$user = $this->gateway->getById($userId)) {
            $this->log("error", "Cannot reset user password: User {$userId} does not exists");
            throw new GeneralException("Unknown user");
        }

        $this->checkState($user);
        // change to state if it is not already ACTIVE!
        if ($user->getState() == UserInterface::STATE_INACTIVE) {
            $this->gateway->updateState($user->getId(), UserInterface::STATE_ACTIVE);
            $user = $user->withState(UserInterface::STATE_ACTIVE);
        }

        // crypt password
        $passwordHash = $this->cryptSecret($password1);

        $this->gateway->updatePassword($user->getId(), $passwordHash);
        $userString = $user->getId() . " (" . $user->getUsername() . "<" .$user->getEmail() . ">)";
        $this->log("debug", "User password is updated for user {$userString}");

        $this->tokenGen->invalidateToken($token);
        $this->log("debug", "User token {$token} invalidated");

        if ($this->storage) {
            $this->storage->set($user->withPassword($passwordHash));
        }

        return $user->withPassword(null);
    }

    public function changePassword($old, $password1, $password2)
    {
        if (!$old) {
            // Моля въведете старата парола
            throw new InvalidArgumentException("Enter your current password", 1);
        }

        // checks password is set
        if (!$password1) {
            // Моля въведете нова парола
            throw new InvalidArgumentException("Enter your new password", 2);
        }

        // checks password is set
        if (!$password2) {
            // Моля въведете новата парола отново
            throw new InvalidArgumentException("Repeat your new password", 3);
        }

        if (!$user = $this->getUser()) {
            $this->log("error", "Cannot change user password: user is not logged in");
            throw new GeneralException("User is not logged in");
        }

        // Check for password strength and throw InvalidArgumentException on error
        $this->validator->checkPassword($password1);
        // Check passwords match and throw InvalidArgumentException on error
        $this->validator->checkPasswordConfirmation($password1, $password2);

        if (!$this->checkSecret($old, $user->getPassword())) {
            $this->log("error", "Cannot change user password: old user password is wrong");
            // Грешна стара парола
            throw new GeneralException("Your old password is invalid");
        }

        // crypt password
        $passwordHash = $this->cryptSecret($password1);

        $this->gateway->updatePassword($user->getId(), $passwordHash);
        $userText = "user {$user->getId()} ({$user->getUsername()}<{$user->getEmail()}>)";
        $this->log("debug", "User password changed for {$userText}");

        if ($this->storage) {
            $this->storage->set($user->withPassword($passwordHash));
        }

        return $user->withPassword(null);
    }

    /**
     * Checks current user state and throws exception if it is not active
     */
    private function checkState($user)
    {
        $state = $user->getState();
        if (in_array($state, [UserInterface::STATE_ACTIVE, UserInterface::STATE_INACTIVE])) {
            return;
        }

        $userText = "user {$user->getId()} ({$user->getUsername()}<{$user->getEmail()}>)";
        if (UserInterface::STATE_BLOCKED == $state) {
            $this->log("error", "Cannot reset user password: The {$userText} is blocked");
            // User account is blocked
            // Вашият потребителски акаунт е блокиран
            throw new UserBlockedException("Your user account has been blocked");
        }

        $this->log("critical", "Cannot reset user password: Unknown {$userText} state. Expected 1-3. Got {$state}");
        throw new UnexpectedValueException("Unknown user state. Expected 1-3. Got {$state}");
    }
}
