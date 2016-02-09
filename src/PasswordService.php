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
use SugiPHP\Auth2\User\UserInterface;
use SugiPHP\Auth2\User\User;
use SugiPHP\Auth2\Storage\StorageInterface;
use SugiPHP\Auth2\LoggerTrait;
use UnexpectedValueException;

class PasswordService
{
    use PasswordHashTrait;
    use LoggerTrait;

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

    /**
     * @var Instance of Storage\StorageInterface
     */
    private $storage;

    public function __construct(PasswordGatewayInterface $gateway, TokenInterface $tokenGen, ValidatorInterface $validator)
    {
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

    public function resetPassword($login, $token, $password1, $password2)
    {
        // checks password is set
        if (!$password1) {
            throw new InvalidArgumentException("Моля въведете парола", 3);
        }

        // checks password is set
        if (!$password2) {
            throw new InvalidArgumentException("Моля въведете паролата отново", 4);
        }

        // check username or email is set
        if (!$login) {
            // exception code 1 for the 1st argument
            $this->log("error", "Cannot reset user password: Missing login parameter");
            throw new GeneralException("Missing user parameter", 1);
        }

        // check token is given
        if (!$token) {
            $this->log("error", "Cannot reset user password: Missing token parameter");
            throw new GeneralException("Missing token", 2);
        }

        // Check for password strength and throw InvalidArgumentException on error
        $this->validator->checkPassword($password1);
        // Check passwords match and throw InvalidArgumentException on error
        $this->validator->checkPasswordConfirmation($password1, $password2);

        if ($emailLogin = (strpos($login, "@") > 0)) {
            if (!$user = $this->gateway->getByEmail($login)) {
                $this->log("error", "Cannot reset user password: Email $login does not exists");
                throw new GeneralException("Unknown user");
            }
        } elseif (!$user = $this->gateway->getByUsername($login)) {
            $this->log("error", "Cannot reset user password: Username $login does not exists");
            throw new GeneralException("Unknown user");
        }

        if (!$this->tokenGen->checkToken($user, $token)) {
            $this->log("error", "Cannot reset user password: Token provided is invalid for user {$login}: {$token}");
            throw new GeneralException("Wrong token");
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
        $this->log("debug", "User password is updated for user {$login}");

        $this->tokenGen->invalidateToken($user, $token);
        $this->log("debug", "User token {$token} invalidated");

        if ($this->storage) {
            $this->storage->set($user->withPassword($passwordHash));
        }

        return $user->withPassword(null);
    }

    public function changePassword($userId, $old, $password1, $password2)
    {
        if (!$userId) {
            throw new GeneralException("The user ID should be set", 1);
        }

        if (!$old) {
            throw new InvalidArgumentException("Моля въведете старата парола", 2);
        }

        // checks password is set
        if (!$password1) {
            throw new InvalidArgumentException("Моля въведете нова парола", 3);
        }

        // checks password is set
        if (!$password2) {
            throw new InvalidArgumentException("Моля въведете новата парола отново", 4);
        }

        // Check for password strength and throw InvalidArgumentException on error
        $this->validator->checkPassword($password1);
        // Check passwords match and throw InvalidArgumentException on error
        $this->validator->checkPasswordConfirmation($password1, $password2);

        if (!$user = $this->gateway->getById($userId)) {
            $this->log("error", "Cannot change user password: user ID {$userId} is not found");
            throw new GeneralException("Unknown user ID");
        }

        if (!$this->checkSecret($old, $user->getPassword())) {
            $this->log("error", "Cannot change user password: old user password is wrong");
            throw new GeneralException("Грешна стара парола");
        }

        // crypt password
        $passwordHash = $this->cryptSecret($password1);

        $this->gateway->updatePassword($userId, $passwordHash);
        $userText = "user {$user->getId()} ({$user->getUsername()}<{$user->getEmail()}>)";
        $this->log("debug", "User password changed for {$userText}");

        if ($this->storage) {
            $this->storage->set($user->withPassword($passwordHash));
        }

        return $user->withPassword(null);
    }

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
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
            // User account is blocked
            $this->log("error", "Cannot reset user password: The {$userText} is blocked");
            throw new GeneralException("Вашият потребителски акаунт е блокиран");
        }

        $this->log("critical", "Cannot reset user password: Unknown {$userText} state. Expected 1-3. Got {$state}");
        throw new UnexpectedValueException("Unknown user state. Expected 1-3. Got {$state}");
    }
}
