<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Validator;

use InvalidArgumentException;
use Psr\Log\LoggerInterface as Logger;

class Validator implements ValidatorInterface
{
    /**
     * @var Instance of Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(Logger $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @see ValidatorInterface::checkEmail()
     *
     * @throws InvalidArgumentException If email is missing, is longer than 255 chars, and is not valid
     */
    public function checkEmail($email)
    {
        if (!$email) {
            $this->log("debug", "Required parameter email is missing");
            throw new InvalidArgumentException("Моля въведете email адрес");
        }

        $len = mb_strlen($email, "UTF-8");
        if ($len > 255) {
            $this->log("debug", "Email mismatch - more than 255 chars ($len chars provided)");
            throw new InvalidArgumentException("Невалиден email адрес ($len символа)");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->log("debug", "Email mismatch: $email is not valid email");
            throw new InvalidArgumentException("Невалиден email адрес");
        }
    }

    /**
     * @see ValidatorInterface::checkUsername()
     *
     * @throws InvalidArgumentException If username is missing, has less than 3 chars,
     *         more than 32 or contains chars that are not allowed
     */
    public function checkUsername($username)
    {
        // Required username is missing
        if (!$username) {
            $this->log("debug", "Required parameter username is missing");
            throw new InvalidArgumentException("Моля въведете потребител");
        }

        $len = mb_strlen($username, "UTF-8");
        // Username too short
        if ($len < 3) {
            $this->log("debug", "Username mismatch - less than 3 chars");
            throw new InvalidArgumentException("Потребителското име трябва да е поне 3 символа");
        }

        // Username too long
        if ($len > 32) {
            $this->log("debug", "Username mismatch - more than 32 chars");
            throw new InvalidArgumentException("Потребителското име не трябва да надвишава 32 символа");
        }

        // Illegal username
        if (!preg_match("#^[a-z]([a-z0-9-_\.])+$#i", $username)) {
            $this->log("debug", "Username contains chars that are not allowed");
            throw new InvalidArgumentException("Потребителското име съдържа непозволени символи");
        }
    }

    /**
     * @see ValidatorInterface::checkPassword()
     *
     * @throws InvalidArgumentException If the password is too short
     * @throws InvalidArgumentException If the password has only one type of chars.
     */
    public function checkPassword($password)
    {
        if (!$password) {
            // Required password is missing
            throw new InvalidArgumentException("Моля въведете парола");
        }

        $len = mb_strlen($password, "UTF-8");
        if ($len < 7) {
            // Password must be at least 7 chars
            throw new InvalidArgumentException("Паролата трябва да е поне 7 символа");
        }

        $diff = 0;
        $patterns = array("#[a-z]#", "#[A-Z]#", "#[0-9]#", "#[^a-zA-Z0-9]#");
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $password, $matches)) {
                $diff++;
            }
        }
        if ($diff < 2) {
            // Password must contain at least 2 different type of chars - lowercase letters, uppercase letters, digits and special symbols
            throw new InvalidArgumentException("Паролата трябва да съдържа поне 2 типа символи (малки букви, главни букви, цифри и специални символи)");
        }
    }

    /**
     * @see ValidatorInterface::checkPasswordConfirmation()
     *
     * @throws InvalidArgumentException If the password confirmation is missing, or is not equal to the password
     */
    public function checkPasswordConfirmation($password, $password2)
    {
        if (!$password2) {
            throw new InvalidArgumentException("Моля въведете потвърждение на паролата");
        }
        // check passwords match
        if ($password2 !== $password) {
            throw new InvalidArgumentException("Въведените пароли се различават");
        }
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
