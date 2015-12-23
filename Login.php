<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2;

use SugiPHP\Auth2\Gateway\LoginGatewayInterface as LoginGateway;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\User\UserInterface;
use InvalidArgumentException;
use UnexpectedValueException;

class Login
{
    /**
     * @var Instance of LoginGatewayInterface
     */
    private $gateway;

    public function __construct(LoginGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Login
     *
     * @param string $login Username or email
     * @param string $password
     *
     * @throws InvalidArgumentException If the username/password is not given
     * @throws GeneralException On login fail
     * @throws GeneralException On not activated (inactive/blocked) accounts
     */
    public function login($login, $password)
    {
        // Incorrect username or password - GitHub
        // The email and password you entered don't match. - Google
        // The password you entered for the email or username demo is incorrect - WordPress
        // The username and password you entered did not match our records. Please double-check and try again. - Twitter
        // "Username/password mismatch"
        // "Email/password mismatch"
        // "Грешно потребителско име/парола"
        // "Грешен email адрес/парола"
        $loginFailedMessage = "Грешен потребител/парола.";

        // check username or email is set
        if (!$login) {
            // exception code 1 for the 1st argument
            throw new InvalidArgumentException("Моля въведете потребител", 1);
        }

        // checks password is set
        if (!$password) {
            throw new InvalidArgumentException("Моля въведете парола", 2);
        }

        if ($emailLogin = (strpos($login, "@") > 0)) {
            if (!$user = $this->gateway->getByEmail($login)) {
                throw new GeneralException($loginFailedMessage);
            }
        } else {
            if (!$user = $this->gateway->getByUsername($login)) {
                throw new GeneralException($loginFailedMessage);
            }
        }

        $this->checkState($user["state"]);

        // check password
        if (!$this->checkSecret($password, $user["password"])) {
            throw new GeneralException($loginFailedMessage);
        }

        // removing password from the return. If you really want to receive a
        // password you can add additional column with a different name
        unset($user["password"]);

        return $user;
    }

    /**
     * Checks current user state and throws exception if it is not active
     *
     * @param integer $state
     */
    private function checkState($state)
    {
        if (UserInterface::STATE_INACTIVE == $state) {
            // Before login you have to confirm your email address
            throw new GeneralException("Моля потвърдете регистрацията си");
        }

        if (UserInterface::STATE_BLOCKED == $state) {
            // User account is blocked
            throw new GeneralException("Вашият потребителски акаунт е блокиран");
        }

        if (UserInterface::STATE_ACTIVE != $state) {
            throw new UnexpectedValueException("Unknown user state. Expected 1-3. Got {$state}");
        }
    }

    /**
     * Compares a secret against a hash.
     *
     * @param string $secret Secret
     * @param string $hash Secret hash made with cryptSecret() method
     *
     * @return boolean
     */
    private function checkSecret($secret, $hash)
    {
        return password_verify($secret, $hash);
    }
}
