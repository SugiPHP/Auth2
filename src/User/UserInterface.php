<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\User;

interface UserInterface
{
    /*
     * User States
     */
    const STATE_ACTIVE   = 1; // the user can authorize and use his/her account
    const STATE_INACTIVE = 2; // some action is required from the user (e.g. validating his/her email address)
    const STATE_BLOCKED  = 3; // the user has been blocked(locked) by administrator

    /**
     * Returns internal user's ID.
     *
     * @return integer
     */
    public function getId();

    /**
     * Returns the username.
     * The username must be unique.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Returns user's email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Returns the user's state.
     * Currently the states are: active, inactive and blocked
     *
     * @return integer
     */
    public function getState();

    /**
     * Sets user state
     *
     * @param integer $state
     *
     * @return UserInterface instance
     */
    public function setState($state);

    /**
     * Returns the encoded password.
     *
     * @return string|null
     */
    public function getPassword();

    /**
     * Sets a password.
     *
     * @param string $password User's password (not encrypted!)
     */
    public function setPassword($password);

    /**
     * Checks the password provided matches the stored hashed password.
     *
     * @param string $password
     *
     * @return boolean
     */
    public function checkPassword($password);

    /**
     * Returns activation or forgot password token.
     *
     * @return string Returns NULL if there is no token set.
     */
    public function getToken();

    /**
     * Sets an activation or forgot password token.
     *
     * @param string $token
     *
     * @return self
     */
    public function setToken($token);
}
