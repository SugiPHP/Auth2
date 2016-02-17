<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Gateway;

use SugiPHP\Auth2\User\UserMapperInterface;
use PDO;

class PDOGateway implements
    LoginGatewayInterface,
    RegistrationGatewayInterface,
    PasswordGatewayInterface
{
    protected $table = "auth2";
    protected $fields = "id, username, email, password, state, reg_date, pass_change_date";

    /**
     * PDO handler
     */
    protected $db;

    /**
     * UserMapper Object
     */
    protected $mapper;

    public function __construct(PDO $db)
    {
        $this->db = $db;
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
        $sql = "SELECT {$this->fields} FROM {$this->table} WHERE id = :id";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("id", (int) $loginId, PDO::PARAM_INT);

        return $this->map($sth);
    }

    /**
     * @see LoginGatewayInterface::getByEmail()
     */
    public function getByEmail($email)
    {
        $sql = "SELECT {$this->fields} FROM {$this->table} WHERE LOWER(email) = LOWER(:email)";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("email", $email, PDO::PARAM_STR);

        return $this->map($sth);
    }

    /**
     * @see LoginGatewayInterface::getByUsername()
     */
    public function getByUsername($username)
    {
        $sql = "SELECT {$this->fields} FROM {$this->table} WHERE LOWER(username) = LOWER(:username)";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("username", $username, PDO::PARAM_STR);

        return $this->map($sth);
    }

    /**
     * @see RegistrationGatewayInterface::add()
     */
    public function add($email, $username, $state, $passwordHash)
    {
        $sql = "INSERT INTO {$this->table} (email, username, state, reg_date, password, pass_change_date)
                VALUES (:email, :username, :state, :time, :password, :time)";

        if ($pg = ("pgsql" == $this->db->getAttribute(PDO::ATTR_DRIVER_NAME))) {
            $sql .= " RETURNING id";
        }
        $sth = $this->db->prepare($sql);
        $sth->bindValue("email", $email);
        $sth->bindValue("username", $username);
        $sth->bindValue("state", (int) $state, PDO::PARAM_INT);
        $sth->bindValue("time", date("Y-m-d H:i:s"));
        $sth->bindValue("password", $passwordHash);
        if (!$sth->execute()) {
            return false;
        }
        if (!$id = (int) (($pg) ? $sth->fetchColumn() : $this->db->lastInsertId())) {
            return false;
        }

        return $this->getById($id);
    }

    /**
     * @see ActivationGatewayInterface::updateState()
     */
    public function updateState($userId, $state)
    {
        $sql = "UPDATE {$this->table} SET state = :state WHERE id = :id";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("id", (int) $userId, PDO::PARAM_INT);
        $sth->bindValue("state", (int) $state, PDO::PARAM_INT);
        $sth->execute();

        return (bool) $sth->rowCount();
    }

    /**
     * @see PasswordGatewayInterface::updatePassword()
     */
    public function updatePassword($userId, $passwordHash)
    {
        $sql = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("id", (int) $userId, PDO::PARAM_INT);
        $sth->bindValue("password", $passwordHash);
        $sth->execute();

        return (bool) $sth->rowCount();
    }

    private function map($sth)
    {
        if (!$sth->execute() || !$row = $sth->fetch(PDO::FETCH_ASSOC)) {
            return false;
        }

        return ($this->mapper) ? $this->mapper->factory($row) : $row;
    }
}
