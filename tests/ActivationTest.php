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
use SugiPHP\Auth2\Activation;
use InvalidArgumentException;

class ActivationTest extends \PHPUnit_Framework_TestCase
{
    const TOKEN = "1234567";
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "state" => 1],
    ];

    private $gateway;
    private $activation;

    public function setUp()
    {
        $data = self::DEMODATA;
        $this->gateway = new Gateway($data, new UserMapper());
        $this->activation = new Activation($this->gateway);
    }

    public function testCreation()
    {
        $this->assertNotNull($this->activation);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIfTokenIsMissing()
    {
        $this->activation->activate("foo", "");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIfUserParameterIsMissing()
    {
        $this->activation->activate("", self::TOKEN);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testExceptionIfUserNotFound()
    {
        $this->activation->activate("foobar", self::TOKEN);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testExceptionIfUserEmailNotFound()
    {
        $this->activation->activate("foobar@example.com", self::TOKEN);
    }
}
