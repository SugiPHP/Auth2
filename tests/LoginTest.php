<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\User\UserMapper;
use SugiPHP\Auth2\Gateway\MemoryGateway as Gateway;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\Exception\InvalidArgumentException;
use SugiPHP\Auth2\Login;

class LoginTest extends \PHPUnit\Framework\TestCase
{
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "state" => 1],
        9 => ["id" => 9, "username" => 'bar',  "email" => null, "state" => 3],
    ];

    private $gateway;
    private $login;

    public function setUp(): void
    {
        $data = self::DEMODATA;
        foreach ($data as &$row) {
            // password is demo
            $row["password"] = '$2y$10$2ZRoTUg0GXOKxYMVZ3orxu2ZloKN6NG3hugC7eiXHF/rmf6bG/GAu';
        }
        $this->gateway = new Gateway($data);
        $this->gateway->setUserMapper(new UserMapper());
        $this->login = new Login($this->gateway);
    }

    public function testCreate()
    {
        $this->assertTrue($this->login instanceof Login);
    }

    public function testLoginReturnsUserOnSuccess()
    {
        $userdata = self::DEMODATA[7];
        $user = $this->login->login("demo", "demo");
        $this->assertEquals($userdata["id"], $user->getId());
        $this->assertEquals($userdata["username"], $user->getUsername());
        $this->assertEquals($userdata["email"], $user->getEmail());
    }

    public function testLoginWithEmail()
    {
        $userdata = self::DEMODATA[7];
        $user = $this->login->login("demo@example.com", "demo");
        $this->assertEquals($userdata["id"], $user->getId());
        $this->assertEquals($userdata["username"], $user->getUsername());
        $this->assertEquals($userdata["email"], $user->getEmail());
    }

    public function testLoginFailsIfTheUserIsNotActive()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\UserInactiveException::class);
        $this->login->login("foo", "demo");
    }

    public function testLoginFailsIfTheUsernameNotFound()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\GeneralException::class);
        $this->login->login("foobar", "demo");
    }

    public function testLoginFailsIfTheEmailNotFound()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\GeneralException::class);
        $this->login->login("foobar@example.com", "demo");
    }

    public function testLoginFailsIfTheUserIsBlocked()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\UserBlockedException::class);
        $this->login->login("bar", "demo");
    }

    public function testLoginFailsIfNoUserIsGiven()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->login->login("", "demo");
    }

    public function testLoginFailsIfNoPasswordIsGiven()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->login->login("demo", "");
    }

    public function testLoginFailsIfThePasswordIsWrong()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\GeneralException::class);
        $this->login->login("demo", "wrongpass");
    }

    public function testGetUserReturnsNotNullIfUserIsNotLoggedIn()
    {
        $this->assertEmpty($this->login->getUser());
    }


    public function testGetUserReturnsLoggedInUser()
    {
        $user = $this->login->login("demo@example.com", "demo");
        $user2 = $this->login->getUser();
        $this->assertEquals($user, $user2);
    }

    public function testLogout()
    {
        $user = $this->login->login("demo@example.com", "demo");
        $this->login->logout();
        $this->assertEmpty($this->login->getUser());
    }
}
