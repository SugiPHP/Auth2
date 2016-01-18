<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Validator;

interface ValidatorInterface
{
    /**
     * Checks email is valid.
     *
     * @param string $email
     *
     * @throws SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function checkEmail($email);

    /**
     * Checks username is valid.
     *
     * @param string $username
     *
     * @throws SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function checkUsername($username);

    /**
     * Password (strength) checker - checks password has enough symbols,
     * and consists several char types small letters, CAPS, numbers and special symbols, etc.
     *
     * @param string $password
     *
     * @throws SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function checkPassword($password);

    /**
     * Checking password confirmation is set and is equal to the password.
     *
     * @param string $password  Password
     * @param string $password2 Password confirmation
     *
     * @throws SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function checkPasswordConfirmation($password, $password2);
}
