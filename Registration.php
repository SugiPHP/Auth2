<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2;

use SugiPHP\Auth2\Gateway\RegistrationGatewayInterface as RegistrationGateway;
use SugiPHP\Auth2\Validator\ValidatorInterface as Validator;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\User\UserInterface;
use Psr\Log\LoggerInterface as Logger;

class Registration
{
    /**
     * @var Instance of RegistrationGatewayInterface
     */
    private $gateway;

    /**
     * @var Instance of ValidatorInterface
     */
    private $validator;

    /**
     * @var Instance of Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(RegistrationGateway $gateway, Validator $validator)
    {
        $this->gateway = $gateway;
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
     * @return integer User's ID
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
        if (!$id = $this->gateway->add($email, $username, UserInterface::STATE_INACTIVE, $passwordHash)) {
            $this->log("error", "Error while inserting user in the DB $email");
            throw new GeneralException("Грешка при създаване на акаунт");
        }

        return $id;
    }

    /**
     * Attach a logger for debugging
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logger
     *
     * @param string $level
     * @param string $message
     */
    private function log($level, $message)
    {
        if ($this->logger) {
            $this->logger->log($level, $message);
        }
    }

    /**
     * Generates a password hash
     *
     * @param string $secret
     *
     * @return string
     */
    private function cryptSecret($secret)
    {
        return password_hash($secret, PASSWORD_BCRYPT);
    }
}
