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
use Psr\Log\LoggerInterface as Logger;
use InvalidArgumentException;
use UnexpectedValueException;

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

    public function __construct(RegistrationGateway $gateway, Validator $validator, Logger $logger = null)
    {
        $this->gateway = $gateway;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * User registration.
     *
     * @param string $email
     * @param string $password
     * @param string $password2 Password confirmation
     *
     * @return array  User info
     *
     * @throws InvalidArgumentException
     * @throws GeneralException
     */
    public function register($email, $username, $password, $password2)
    {
        $email = mb_strtolower($email, "UTF-8");
        // checks email addresses and throws InvalidArgumentException on error
        $this->validator->checkEmail($email);

        // Check for password strength
        $this->validator->checkPassStrength($password);
        // Check passwords match
        $this->validator->checkPasswordConfirmation($password, $password2);
        // crypt password
        $passwordHash = $this->cryptSecret($password);

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

        // insert in the DB and get new user's ID or some other data that will be returned
        if (!$id = $this->gateway->add($email, $username, static::STATE_INACTIVE, $passwordHash)) {
            $this->log("error", "Error while inserting user in the DB $email");
            throw new GeneralException("Грешка при създаване на акаунт");
        }

        // creating unique token
        $token = sha1($passwordHash . $email);

        // return token for account activation via e-mail
        return array("email" => $email, "state" => static::STATE_INACTIVE, "token" => $token, "id" => $id);
    }

    /**
     * Generates a hash.
     *
     * @param string $secret
     * @return string
     */
    private function cryptSecret($secret)
    {
        return password_hash($secret, PASSWORD_BCRYPT);
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
}
