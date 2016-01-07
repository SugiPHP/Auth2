<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Storage;

/**
 * Logged in user storage using PHP's internal session.
 */
class NativeSessionStorage implements StorageInterface
{
    private $sessionKey = "SugiPHP-Auth2-User";

    public function get()
    {
        $this->startSession();

        if (!isset($_SESSION[$this->sessionKey])) {
            return false;
        }

        return $_SESSION[$this->sessionKey];
    }

    public function set($data)
    {
        $this->startSession();

        $_SESSION[$this->sessionKey] = $data;
    }

    public function remove()
    {
        $this->startSession();

        if (isset($_SESSION[$this->sessionKey])) {
            unset($_SESSION[$this->sessionKey]);
        }
    }

    public function has()
    {
        $this->startSession();

        return isset($_SESSION[$this->sessionKey]);
    }

    public function getSessionKey()
    {
        return $this->sessionKey;
    }

    public function setSessionKey($value)
    {
        $this->sessionKey = $value;
    }

    private function startSession()
    {
        if (PHP_SESSION_NONE === session_status()) {
            session_start();
        }
    }
}
