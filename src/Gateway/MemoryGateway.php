<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Gateway;

use SugiPHP\Auth2\User\UserMapperInterface;
use SugiPHP\Auth2\User\UserInterface;
use PDO;

class MemoryGateway implements
    LoginGatewayInterface,
    RegistrationGatewayInterface,
    TokenGatewayInterface,
    PasswordGatewayInterface
{
    /**
     * @var UserMapper Object
     */
    private $mapper;

    /**
     * @var array User Data Storage
     */
    private $storage;

    /**
     * @var array Token Storage
     */
    private $tokens;

    public function __construct(array $storage = [])
    {
        $this->storage = $storage;
    }

    /**
     * Register a User Mapper
     *
     * @param UserMapperInterface|null $mapper
     */
    public function setUserMapper(UserMapperInterface $mapper = null)
    {
        $this->mapper = $mapper;
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

        return $this->getById($id);
    }

    /**
     * @see ActivationGatewayInterface::updateState()
     */
    public function updateState($userId, $state)
    {
        if (empty($this->storage[$userId])) {
            return false;
        }

        $this->storage[$userId]["state"] = $state;
        return true;
    }

    /**
     * @see PasswordGatewayInterface::updatePassword()
     */
    public function updatePassword($userId, $passwordHash)
    {
        if (empty($this->storage[$userId])) {
            return false;
        }

        $this->storage[$userId]["password"] = $passwordHash;
        return true;
    }

    /**
     * @see TokenGatewayInterface::storeToken()
     */
    public function storeToken($token, $userId)
    {
        $this->tokens[$token] = $userId;
    }

    /**
     * @see TokenGatewayInterface::findToken()
     */
    public function findToken($token)
    {
        return (empty($this->tokens[$token])) ? false : $this->tokens[$token];
    }

    /**
     * @see TokenGatewayInterface::deleteToken()
     */
    public function deleteToken($token)
    {
        unset($this->tokens[$token]);
    }

    /**
     * @see TokenGatewayInterface::deleteUserTokens()
     */
    public function deleteUserTokens($userId)
    {
        $this->tokens = array_filter($this->tokens, function ($tokenUser) use ($userId) {
            return ($tokenUser != $userId);
        });
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
