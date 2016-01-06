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
use SugiPHP\Auth2\Validator\Validator;
use SugiPHP\Auth2\Registration;
use InvalidArgumentException;

class RegisterTest extends \PHPUnit_Framework_TestCase
{
    const PASS = "strongPassword12345&*(";
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "state" => 1],
    ];

    private $gateway;
    private $registration;

    public function setUp()
    {
        $data = self::DEMODATA;
        $this->gateway = new Gateway(new UserMapper(), $data);
        $this->registration = new Registration($this->gateway, new Validator());
    }

    public function testCreation()
    {
        $this->assertNotNull($this->registration);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testExceptionIfUsernameExists()
    {
        $this->registration->register("no@email.com", "demo", self::PASS, self::PASS);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testExceptionIfEmailExists()
    {
        $this->registration->register("demo@example.com", "wronguser", self::PASS, self::PASS);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIfEmailNotValid()
    {
        $this->registration->register("demo#example.com", "newuser", self::PASS, self::PASS);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIfUsernameIsEmpty()
    {
        $this->registration->register("demo@example.com", "", self::PASS, self::PASS);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIfEmailIsEmpty()
    {
        $this->registration->register("", "newuser", self::PASS, self::PASS);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIfPasswrodIsEmpty()
    {
        $this->registration->register("newmail@example.com", "newuser", "", self::PASS);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIfPasswrodConfirmationIsEmpty()
    {
        $this->registration->register("newmail@example.com", "newuser", self::PASS, "");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIfPasswrodConfirmationDiffers()
    {
        $this->registration->register("newmail@example.com", "newuser", self::PASS, self::PASS . "+");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIfPasswrodTooWeek()
    {
        $this->registration->register("newmail@example.com", "newuser", "abc", "abc");
    }
}
