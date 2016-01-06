<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\User;

class UserMapper implements UserMapperInterface
{
    public function factory(array $params)
    {
        $default = ["id" => 0, "username" => "", "email" => "", "state" => 0, "password" => ""];
        $params = array_merge($default, $params);
        $user = new User($params["id"], $params["username"], $params["email"], $params["state"], $params["password"]);

        return $user;
    }
}
