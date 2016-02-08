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

class RegistrationTest extends \PHPUnit_Framework_TestCase
{
    const PASS = "strongPassword12345&*(";
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "state" => 1],
    ];

    private $gateway;
    private $service;
    private $tokenGen;

    public function setUp()
    {
        $this->tokenGen = new UserToken();
        $data = self::DEMODATA;
        $this->gateway = new Gateway($data, new UserMapper());
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

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testExceptionIfUsernameExists()
    {
        $this->service->register("no@email.com", "demo", self::PASS, self::PASS);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testExceptionIfEmailExists()
    {
        $this->service->register("demo@example.com", "wronguser", self::PASS, self::PASS);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testExceptionIfEmailNotValid()
    {
        $this->service->register("demo#example.com", "newuser", self::PASS, self::PASS);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testExceptionIfUsernameIsEmpty()
    {
        $this->service->register("demo@example.com", "", self::PASS, self::PASS);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testExceptionIfEmailIsEmpty()
    {
        $this->service->register("", "newuser", self::PASS, self::PASS);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testExceptionIfPasswrodIsEmpty()
    {
        $this->service->register("newmail@example.com", "newuser", "", self::PASS);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testExceptionIfPasswrodConfirmationIsEmpty()
    {
        $this->service->register("newmail@example.com", "newuser", self::PASS, "");
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testExceptionIfPasswrodConfirmationDiffers()
    {
        $this->service->register("newmail@example.com", "newuser", self::PASS, self::PASS . "+");
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testExceptionIfPasswrodTooWeek()
    {
        $this->service->register("newmail@example.com", "newuser", "abc", "abc");
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testLogger()
    {
        $this->service->setLogger(new NullLogger());
        $this->service->register("foo@bar.com", "newuser", self::PASS, self::PASS);
    }
}
