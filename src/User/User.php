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
    private $password;
    private $state;

    public function __construct($id, $username, $email, $state, $password)
    {
        if (empty($id)) {
            throw new InvalidArgumentException("The user ID cannot be empty");
        }

        if (empty($username)) {
            throw new InvalidArgumentException("The username cannot be empty");
        }

        if (!in_array($state, [UserInterface::STATE_ACTIVE, UserInterface::STATE_INACTIVE, UserInterface::STATE_BLOCKED])) {
            throw new InvalidArgumentException("Unknown user state");
        }

        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->state = $state;
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
     * Returns the user's state.
     * Currently the states are: active, inactive and blocked
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }
}
