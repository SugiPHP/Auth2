<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Gateway;

use SugiPHP\Auth2\User\UserMapperInterface;
use PDO;

class MemoryGateway implements
    LoginGatewayInterface,
    RegistrationGatewayInterface
{
    /**
     * UserMapper Object
     */
    private $mapper;

    private $storage;

    public function __construct(array $storage = [], UserMapperInterface $mapper = null)
    {
        $this->mapper = $mapper;
        $this->storage = $storage;
    }

    /**
     * @see LoginGatewayInterface::getById()
     */
    public function getById($loginId)
    {
        return (isset($this->storage[$loginId])) ? $this->map($this->storage[$loginId]) : false;
    }

    /**
     * @see LoginGatewayInterface::getByEmail()
     */
    public function getByEmail($email)
    {
        return $this->findByKey("email", $email);
    }

    /**
     * @see LoginGatewayInterface::getByUsername()
     */
    public function getByUsername($username)
    {
        return $this->findByKey("username", $username);
    }

    /**
     * @see RegistrationGatewayInterface::add()
     */
    public function add($email, $username, $state, $password)
    {
        $id = count($this->storage) + 1;

        $this->storage[$id] = [
            "id" => $id,
            "email" => $email,
            "username" => $username,
            "state" => $state,
            "password" => $password
        ];

        return $id;
    }

    /**
     * @see RegistrationGatewayInterface::updateState()
     */
    public function updateState($id, $state)
    {
        if (empty($this->storage[$id])) {
            return false;
        }

        $this->storage[$id]["state"] = $state;
        return true;
    }

    private function findByKey($key, $value)
    {
        foreach ($this->storage as $row) {
            if ($value == $row[$key]) {
                return $this->map($row);
            }
        }

        return false;
    }

    private function map($value)
    {
        return ($this->mapper) ? $this->mapper->factory($value) : $value;
    }
}
