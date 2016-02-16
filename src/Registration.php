<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2;

use SugiPHP\Auth2\Gateway\RegistrationGatewayInterface;
use SugiPHP\Auth2\Token\TokenInterface;
use SugiPHP\Auth2\Validator\ValidatorInterface;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\Exception\InvalidArgumentException;
use SugiPHP\Auth2\Exception\InvalidTokenException;
use SugiPHP\Auth2\Exception\UserBlockedException;
use SugiPHP\Auth2\User\UserInterface;
use SugiPHP\Auth2\LoggerTrait;
use SugiPHP\Auth2\StorageTrait;
use UnexpectedValueException;

class Registration
{
    use PasswordHashTrait;
    use LoggerTrait;
    use StorageTrait;

    /**
     * @var Instance of RegistrationGatewayInterface
     */
    private $gateway;

    /**
     * @var Instance of TokenInterface
     */
    private $tokenGen;

    /**
     * @var Instance of ValidatorInterface
     */
    private $validator;

    public function __construct(RegistrationGatewayInterface $gateway, TokenInterface $tokenGen, ValidatorInterface $validator)
    {
        $this->gateway = $gateway;
        $this->tokenGen = $tokenGen;
        $this->validator = $validator;
    }

    /**
     * User registration.
     *
     * @param string $email
     * @param string $username
     * @param string $password
     * @param string $password2 Password confirmation
     *
     * @return User
     *
     * @throws InvalidArgumentException
     * @throws GeneralException
     */
    public function register($email, $username, $password, $password2)
    {
        $email = mb_strtolower($email, "UTF-8");

        // checks email addresses & username and throw InvalidArgumentException on error
        $this->validator->checkEmail($email);
        $this->validator->checkUsername($username);
        // Check for password strength and throw InvalidArgumentException on error
        $this->validator->checkPassword($password);
        // Check passwords match and throw InvalidArgumentException on error
        $this->validator->checkPasswordConfirmation($password, $password2);

        // check email is unique
        if ($this->gateway->getByEmail($email)) {
            $this->log("debug", "Cannot register user: Email $email exists");
            throw new GeneralException("Има регистриран потребител с този email адрес");
        }

        // check username is unique
        if ($this->gateway->getByUsername($username)) {
            $this->log("debug", "Cannot register user: Username $username exists");
            throw new GeneralException("Има регистриран потребител с това потребителско име");
        }

        // crypt password
        $passwordHash = $this->cryptSecret($password);

        // insert in the DB and get new user's ID or some other data that will be returned
        if (!$user = $this->gateway->add($email, $username, UserInterface::STATE_INACTIVE, $passwordHash)) {
            $this->log("error", "Error while inserting user in the DB {$username}<{$email}>");
            throw new GeneralException("Грешка при създаване на акаунт");
        }

        $token = $this->tokenGen->generateToken($user);

        return $user->withToken($token);
    }

    /**
     * Activates user account
     *
     * @throws InvalidArgumentException if user/email is missing
     * @throws GeneralException if user is unknown
     * @throws GeneralException if user is blocked
     * @throws InvalidTokenException if token is wrong or token is missing
     */
    public function activate($token)
    {
        // checks token is set
        if (!$token) {
            throw new InvalidTokenException("Missing activation token");
        }

        if (!$userId = $this->tokenGen->fetchToken($token)) {
            throw new InvalidTokenException("Invalid activation token");
        }

        if (!$user = $this->gateway->getById($userId)) {
            throw new GeneralException("Unknown user");
        }

        // the user is already active
        if ($this->checkState($user->getState())) {
            // If the state is already active it can be insecure to use
            // the token for logins. The token might be old if it is not
            // invalidated, or it is based on user's data (e.g. UserToken)
            // So we SHOULD return true, instead of User Data
            return true;
        }

        if (!$this->gateway->updateState($user->getId(), UserInterface::STATE_ACTIVE)) {
            throw new GeneralException("Error in activation process");
        }

        $this->tokenGen->invalidateToken($token);

        $user = $user->withToken(null)->withState(UserInterface::STATE_ACTIVE);
        if ($this->storage) {
            $this->storage->set($user);
        }

        return $user->withPassword(null);
    }

    /**
     * Checks current user state and throws exception if it is blocked
     *
     * @param boolean $state
     * @throws UserBlockedException if user is blocked
     */
    private function checkState($state)
    {
        if (UserInterface::STATE_BLOCKED == $state) {
            // User account is blocked
            // Вашият потребителски акаунт е блокиран
            throw new UserBlockedException("Your user account has been blocked");
        }

        if (UserInterface::STATE_ACTIVE == $state) {
            return true;
        }

        if (UserInterface::STATE_INACTIVE != $state) {
            throw new UnexpectedValueException("Unknown user state. Expected 1-3. Got {$state}");
        }

        return false;
    }
}
