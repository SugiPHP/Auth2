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
use SugiPHP\Auth2\Validator\Validator;
use SugiPHP\Auth2\Token\UserToken;
use SugiPHP\Auth2\Registration;
use Psr\Log\NullLogger;

class RegistrationTest extends \PHPUnit\Framework\TestCase
{
    const PASS = "strongPassword12345&*(";
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "state" => 1],
    ];

    private $gateway;
    private $service;
    private $tokenGen;

    public function setUp(): void
    {
        $data = self::DEMODATA;
        $this->gateway = new Gateway($data);
        $this->gateway->setUserMapper(new UserMapper());
        $this->tokenGen = new UserToken($this->gateway);
        $this->service = new Registration($this->gateway, $this->tokenGen, new Validator());
    }

    public function testCreation()
    {
        $this->assertNotNull($this->service);
    }

    public function testRegisterSuccessful()
    {
        $user = $this->service->register("newuser@example.com", "newuser", self::PASS, self::PASS);
        $this->assertEquals("newuser", $user->getUsername());
        $this->assertEquals("newuser@example.com", $user->getEmail());
    }

    public function testExceptionIfUsernameExists()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\GeneralException::class);
        $this->service->register("no@email.com", "demo", self::PASS, self::PASS);
    }

    public function testExceptionIfEmailExists()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\GeneralException::class);
        $this->service->register("demo@example.com", "wronguser", self::PASS, self::PASS);
    }

    public function testExceptionIfEmailNotValid()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->register("demo#example.com", "newuser", self::PASS, self::PASS);
    }

    public function testExceptionIfUsernameIsEmpty()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->register("demo@example.com", "", self::PASS, self::PASS);
    }

    public function testExceptionIfEmailIsEmpty()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->register("", "newuser", self::PASS, self::PASS);
    }

    public function testExceptionIfPasswrodIsEmpty()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->register("newmail@example.com", "newuser", "", self::PASS);
    }

    public function testExceptionIfPasswrodConfirmationIsEmpty()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->register("newmail@example.com", "newuser", self::PASS, "");
    }

    public function testExceptionIfPasswrodConfirmationDiffers()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->register("newmail@example.com", "newuser", self::PASS, self::PASS . "+");
    }

    public function testExceptionIfPasswrodTooWeek()
    {
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $this->service->register("newmail@example.com", "newuser", "abc", "abc");
    }

    public function testLogger()
    {
        $this->service->setLogger(new NullLogger());
        $this->expectException(\SugiPHP\Auth2\Exception\GeneralException::class);
        $this->service->register("foo@bar.com", "newuser", self::PASS, self::PASS);
    }
}
