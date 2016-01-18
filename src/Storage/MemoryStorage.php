<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Storage;

/**
 * Logged in user storage
 */
class MemoryStorage implements StorageInterface
{
    private $memory;

    public function get()
    {
        if (!isset($this->memory)) {
            return false;
        }

        return $this->memory;
    }

    public function set($data)
    {
        $this->memory = $data;
    }

    public function remove()
    {
        if (isset($this->memory)) {
            unset($this->memory);
        }
    }

    public function has()
    {
        return isset($this->memory);
    }
}
