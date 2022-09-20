<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\User\UserMapper;
use SugiPHP\Auth2\User\UserInterface;
use SugiPHP\Auth2\Gateway\MemoryGateway as Gateway;
use SugiPHP\Auth2\Validator\Validator;
use SugiPHP\Auth2\PasswordService;
use SugiPHP\Auth2\Token\UserToken;
use SugiPHP\Auth2\Exception\InvalidArgumentException;
use SugiPHP\Auth2\Exception\InvalidTokenException;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\Exception\UserBlockedException;

class PasswordServiceTest extends \PHPUnit\Framework\TestCase
{
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "state" => 1],
        9 => ["id" => 9, "username" => 'blocked', "email" => 'blocked@example.com', "state" => 3],
    ];

    private $gateway;
    private $service;
    private $tokenGen;

    public function setUp(): void
    {
        $data = self::DEMODATA;
        foreach ($data as &$row) {
            // password is demo
            $row["password"] = '$2y$10$2ZRoTUg0GXOKxYMVZ3orxu2ZloKN6NG3hugC7eiXHF/rmf6bG/GAu';
        }
        $this->gateway = new Gateway($data);
        $this->gateway->setUserMapper(new UserMapper());
        $this->tokenGen = new UserToken($this->gateway);
        $this->service = new PasswordService($this->gateway, $this->tokenGen, new Validator());
    }

    public function testCreation()
    {
        $this->assertNotNull($this->service);
    }

    public function testGenTokenReturnsUserWithToken()
    {
        $user = $this->service->genToken("foo@bar.com");
        $this->assertNotNull($user);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertNotEmpty($user->getToken());
    }

    public function testGenTokenReturnsUserWithTokenIfTheStateIsInactive()
    {
        $user = $this->service->genToken("demo@example.com");
        $this->assertNotNull($user);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertNotEmpty($user->getToken());
    }

    public function testGenTokenWithNoEmailTrhowsException()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->genToken("");
    }

    public function testGenTokenWithUnregisteredEmailTrhowsException()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\GeneralException::class);
        $this->service->genToken("wrong@email.com");
    }

    public function testGenTokenWithWrongStateTrhowsException()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\UserBlockedException::class);
        $this->service->genToken("blocked@example.com");
    }

    public function testResetPasswordReturnsUser()
    {
        $password = "qwerty1234~!@#";
        $email = "demo@example.com";
        $user = $this->service->genToken($email);
        $token = $user->getToken();
        $user = $this->service->resetPassword($token, $password, $password);
        $this->assertNotNull($user);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertEquals(UserInterface::STATE_ACTIVE, $user->getState());
    }

    public function testResetPasswordReturnsUserIfTheStateIsInactive()
    {
        $password = "qwerty1234~!@#";
        $email = "demo@example.com";
        $user = $this->service->genToken($email);
        $token = $user->getToken();
        $user = $this->service->resetPassword($token, $password, $password);
        $this->assertNotNull($user);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertEquals(UserInterface::STATE_ACTIVE, $user->getState());
    }

    public function testResetPasswordThrowsExceptionIfPasswordIsNotSet()
    {
        $password = "qwerty1234~!@#";
        $email = "demo@example.com";
        $user = $this->service->genToken($email);
        $token = $user->getToken();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $user = $this->service->resetPassword($token, "", $password);
    }

    public function testResetPasswordThrowsExceptionIfPassword2IsNotSet()
    {
        $password = "qwerty1234~!@#";
        $email = "demo@example.com";
        $user = $this->service->genToken($email);
        $token = $user->getToken();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $user = $this->service->resetPassword($token, $password, "");
    }

    public function testResetPasswordThrowsExceptionIfTokenIsNotSet()
    {
        $password = "qwerty1234~!@#";
        $email = "demo@example.com";
        $user = $this->service->genToken($email);
        $token = $user->getToken();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidTokenException::class);
        $user = $this->service->resetPassword("", $password, $password);
    }

    public function testResetPasswordThrowsExceptionOnWrongToken()
    {
        $password = "qwerty1234~!@#";
        $email = "demo@example.com";
        $user = $this->service->genToken($email);
        $token = $user->getToken();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidTokenException::class);
        $user = $this->service->resetPassword($token."123", $password, $password);
    }

    public function testCahngePasswordReturnsUser()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $user = $this->service->login("demo", $old);
        $this->assertTrue($this->service->changePassword($old, $password, $password) instanceof UserInterface);
    }

    public function testCahngePasswordThrowsExceptionIfOldPasswordIsEmpty()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $user = $this->service->login("demo", $old);
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->changePassword("", $password, $password);
    }

    public function testCahngePasswordThrowsExceptionIfNewPasswordIsEmpty()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $user = $this->service->login("demo", $old);
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->changePassword($old, "", $password);
    }

    public function testCahngePasswordThrowsExceptionIfRepeatPasswordIsEmpty()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $user = $this->service->login("demo", $old);
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->changePassword($old, $password, "");
    }

    public function testCahngePasswordThrowsExceptionIfUserIsNotLoggedIn()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $this->expectException(\SugiPHP\Auth2\Exception\GeneralException::class);
        $this->service->changePassword($old, $password, $password);
    }

    public function testCahngePasswordThrowsExceptionIfOldPassIsWrong()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $user = $this->service->login("demo", $old);
        $this->expectException(\SugiPHP\Auth2\Exception\GeneralException::class);
        $this->service->changePassword("wrong", $password, $password);
    }
}
