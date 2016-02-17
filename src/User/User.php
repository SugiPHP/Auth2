<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\User;

use SugiPHP\Auth2\Exception\InvalidArgumentException;

class User implements UserInterface
{
    private $id;
    private $username;
    private $email;
    private $state;
    private $password;
    private $token;

    public function __construct($id, $username, $email, $state, $password)
    {
        if (empty($id)) {
            throw new InvalidArgumentException("The user ID cannot be empty");
        }

        if (empty($username)) {
            throw new InvalidArgumentException("The username cannot be empty");
        }

        $this->setState($state);
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Returns internal user's ID.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the username.
     * The username must be unique.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns user's email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the encoded password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Checks the password provided matches the stored hashed password.
     *
     * @param string $password
     *
     * @return boolean
     */
    public function checkPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Sets a password.
     *
     * @param string $password User's password (not encrypted!)
     */
    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Returns the user's state.
     * Currently the states are: active, inactive and blocked
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets user state
     *
     * @param integer $state
     *
     * @return UserInterface instance
     */
    public function setState($state)
    {
        if (!in_array($state, [UserInterface::STATE_ACTIVE, UserInterface::STATE_INACTIVE, UserInterface::STATE_BLOCKED])) {
            throw new InvalidArgumentException("Unknown user state");
        }

        $this->state = $state;

        return $this;
    }

    /**
     * Returns activation forgot password token.
     *
     * @return string Returns NULL if there is no token set.
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets an activation or forgot password token.
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }
}
