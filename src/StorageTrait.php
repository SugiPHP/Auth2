<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2;

use SugiPHP\Auth2\Storage\StorageInterface;

trait StorageTrait
{
    /**
     * The storage instance
     *
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Sets a storage.
     *
     * @param StorageInterface $storage
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }
}
