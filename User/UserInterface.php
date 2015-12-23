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
     * Returns the encoded password.
     *
     * @return string
     */
    public function getPassword();

    /**
     * Returns the user's state.
     * Currently the states are: active, inactive and blocked
     *
     * @return integer
     */
    public function getState();
}
