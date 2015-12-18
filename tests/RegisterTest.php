<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\Gateway\MemoryGateway as Gateway;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\Validator\Validator;
use SugiPHP\Auth2\Registration;
use InvalidArgumentException;

class RegisterTest extends \PHPUnit_Framework_TestCase
{
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "state" => 1],
    ];

    private $gateway;
    private $registration;

    public function setUp()
    {
        $data = self::DEMODATA;
        $this->gateway = new Gateway($data);
        $this->registration = new Registration($this->gateway, new Validator());
    }

    public function testCreation()
    {
        $this->assertNotNull($this->registration);
    }
}
