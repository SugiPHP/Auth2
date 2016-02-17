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
use SugiPHP\Auth2\Exception\InvalidTokenException;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\Exception\UserBlockedException;

class ActivationTest extends \PHPUnit_Framework_TestCase
{
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
        $this->gateway = new Gateway($data);
        $this->gateway->setUserMapper(new UserMapper());
        $this->tokenGen = new UserToken($this->gateway);
        $this->service = new Registration($this->gateway, $this->tokenGen, new Validator());
    }

    public function testCreation()
    {
        $this->assertNotNull($this->service);
    }

    public function testCheckActivation()
    {
        $userId = 1;
        $user = $this->gateway->getById($userId);
        $this->assertEquals(UserInterface::STATE_INACTIVE, $user->getState());
        $token = $this->tokenGen->generateToken($userId);
        $user2 = $this->service->activate($token);
        $this->assertEquals(1, $user2->getId());
        $this->assertEquals(UserInterface::STATE_ACTIVE, $user2->getState());
    }

    public function testCheckAlreadyActivatedReturnsTrue()
    {
        $userId = 7;
        $user = $this->gateway->getById($userId);
        $this->assertEquals(UserInterface::STATE_ACTIVE, $user->getState());
        $token = $this->tokenGen->generateToken($userId);
        $this->assertTrue($this->service->activate($token));
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\UserBlockedException
     */
    public function testCheckUserBlockedToken()
    {
        $userId = 9;
        $user = $this->gateway->getById($userId);
        $this->assertEquals(UserInterface::STATE_BLOCKED, $user->getState());
        $token = $this->tokenGen->generateToken($userId);
        $this->assertTrue($this->service->activate($token));
        $this->service->activate($token);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\GeneralException
     */
    public function testCheckWrongToken()
    {
        $userId = 7;
        $user = $this->gateway->getById($userId);
        $token = $this->tokenGen->generateToken($userId);
        $this->service->activate($token."123");
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidTokenException
     */
    public function testExceptionIfTokenIsMissing()
    {
        $this->service->activate("");
    }
}
