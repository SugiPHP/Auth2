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
use SugiPHP\Auth2\Registration;
use SugiPHP\Auth2\Token\UserToken;
use SugiPHP\Auth2\Exception\InvalidArgumentException;
use SugiPHP\Auth2\Exception\GeneralException;

class ActivationTest extends \PHPUnit_Framework_TestCase
{
    const TOKEN = "1234567";
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "state" => 1],
        9 => ["id" => 9, "username" => 'blocked', "email" => 'demo@example.com', "state" => 3],
    ];

    private $gateway;
    private $service;
    private $tokenGen;

    public function setUp()
    {
        $data = self::DEMODATA;
        $this->gateway = new Gateway($data, new UserMapper());
        $this->tokenGen = new UserToken($this->gateway);
        $this->service = new Registration($this->gateway, $this->tokenGen, new Validator());
    }

    public function testCreation()
    {
        $this->assertNotNull($this->service);
    }

    public function testCheckActivation()
    {
        $user = $this->gateway->getById(1);
        $this->assertEquals(UserInterface::STATE_INACTIVE, $user->getState());
        $token = $this->tokenGen->generateToken($user);
        $user2 = $this->service->activate($token);
        $this->assertEquals(1, $user2->getId());
        $this->assertEquals(UserInterface::STATE_ACTIVE, $user2->getState());
    }

    public function testCheckAlreadyActivatedReturnsTrue()
    {
        $user = $this->gateway->getById(7);
        $this->assertEquals(UserInterface::STATE_ACTIVE, $user->getState());
        $token = $this->tokenGen->generateToken($user);
        $this->assertTrue($this->service->activate($token));
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testCheckWrongToken()
    {
        $this->service->activate(self::TOKEN);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testExceptionIfTokenIsMissing()
    {
        $this->service->activate("");
    }
}
