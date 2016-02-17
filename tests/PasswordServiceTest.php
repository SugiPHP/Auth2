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

class PasswordServiceTest extends \PHPUnit_Framework_TestCase
{
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "state" => 1],
        9 => ["id" => 9, "username" => 'blocked', "email" => 'blocked@example.com', "state" => 3],
    ];

    private $gateway;
    private $service;
    private $tokenGen;

    public function setUp()
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

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testGenTokenWithNoEmailTrhowsException()
    {
        $this->service->genToken("");
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testGenTokenWithUnregisteredEmailTrhowsException()
    {
        $this->service->genToken("wrong@email.com");
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\UserBlockedException
     */
    public function testGenTokenWithWrongStateTrhowsException()
    {
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

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testResetPasswordThrowsExceptionIfPasswordIsNotSet()
    {
        $password = "qwerty1234~!@#";
        $email = "demo@example.com";
        $user = $this->service->genToken($email);
        $token = $user->getToken();
        $user = $this->service->resetPassword($token, "", $password);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testResetPasswordThrowsExceptionIfPassword2IsNotSet()
    {
        $password = "qwerty1234~!@#";
        $email = "demo@example.com";
        $user = $this->service->genToken($email);
        $token = $user->getToken();
        $user = $this->service->resetPassword($token, $password, "");
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidTokenException
     */
    public function testResetPasswordThrowsExceptionIfTokenIsNotSet()
    {
        $password = "qwerty1234~!@#";
        $email = "demo@example.com";
        $user = $this->service->genToken($email);
        $token = $user->getToken();
        $user = $this->service->resetPassword("", $password, $password);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidTokenException
     */
    public function testResetPasswordThrowsExceptionOnWrongToken()
    {
        $password = "qwerty1234~!@#";
        $email = "demo@example.com";
        $user = $this->service->genToken($email);
        $token = $user->getToken();
        $user = $this->service->resetPassword($token."123", $password, $password);
    }

    public function testCahngePasswordReturnsUser()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $user = $this->service->login("demo", $old);
        $this->assertTrue($this->service->changePassword($old, $password, $password) instanceof UserInterface);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCahngePasswordThrowsExceptionIfOldPasswordIsEmpty()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $user = $this->service->login("demo", $old);
        $this->service->changePassword("", $password, $password);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCahngePasswordThrowsExceptionIfNewPasswordIsEmpty()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $user = $this->service->login("demo", $old);
        $this->service->changePassword($old, "", $password);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCahngePasswordThrowsExceptionIfRepeatPasswordIsEmpty()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $user = $this->service->login("demo", $old);
        $this->service->changePassword($old, $password, "");
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testCahngePasswordThrowsExceptionIfUserIsNotLoggedIn()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $this->service->changePassword($old, $password, $password);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testCahngePasswordThrowsExceptionIfOldPassIsWrong()
    {
        $old = "demo";
        $password = "qwerty1234~!@#";
        $user = $this->service->login("demo", $old);
        $this->service->changePassword("wrong", $password, $password);
    }
}
