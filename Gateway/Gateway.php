<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Gateway;

use PDO;

class Gateway implements
    LoginGatewayInterface,
    RegistrationGatewayInterface
{
    private $tableUsers = "auth2";
    private $tableUsersFields = "id, username, email, password, state, reg_date, pass_change_date";

    /**
     * PDO handler
     */
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @see LoginGatewayInterface::getById()
     */
    public function getById($loginId)
    {
        $sql = "SELECT {$this->tableUsersFields} FROM {$this->tableUsers} WHERE id = :id";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("id", (int) $loginId, PDO::PARAM_INT);
        $sth->execute();

        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @see LoginGatewayInterface::getByEmail()
     */
    public function getByEmail($email)
    {
        $sql = "SELECT {$this->tableUsersFields} FROM {$this->tableUsers} WHERE LOWER(email) = LOWER(:email)";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("email", $email, PDO::PARAM_STR);
        $sth->execute();

        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @see LoginGatewayInterface::getByUsername()
     */
    public function getByUsername($username)
    {
        $sql = "SELECT {$this->tableUsersFields} FROM {$this->tableUsers} WHERE LOWER(username) = LOWER(:username)";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("username", $username, PDO::PARAM_STR);
        $sth->execute();

        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @see RegistrationGatewayInterface::add()
     */
    public function add($email, $username, $state, $passwordHash)
    {
        $sql = "INSERT INTO {$this->tableUsers} (email, username, state, reg_date, password, pass_change_date)
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
        $sth->execute();

        return (int) (($pg) ? $sth->fetchColumn() : $this->db->lastInsertId());
    }

    /**
     * @see RegistrationGatewayInterface::updateState()
     */
    public function updateState($id, $state)
    {
        $sql = "UPDATE {$this->tableUsers} SET state = :state
                WHERE id = :id";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("id", (int) $id, PDO::PARAM_INT);
        $sth->bindValue("state", (int) $state, PDO::PARAM_INT);
        $sth->execute();

        return (bool) $sth->rowCount();
    }
}
