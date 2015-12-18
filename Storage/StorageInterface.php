<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Storage;

/**
 * Where the logged user information is stored.
 * Typically this is done in the PHP session.
 */
interface StorageInterface
{
    public function get();

    public function set($data);

    public function remove();

    public function has();
}
