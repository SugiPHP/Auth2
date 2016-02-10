<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\User\User;
use SugiPHP\Auth2\User\UserInterface;
use SugiPHP\Auth2\User\UserMapper;
use SugiPHP\Auth2\Token\UserToken;
use SugiPHP\Auth2\Token\TokenInterface;
use SugiPHP\Auth2\Gateway\MemoryGateway as Gateway;

class UserTokenTest extends \PHPUnit_Framework_TestCase
{
    private $tokenGen;
    private $user;

    public function setUp()
    {
        $data = ["id" => 1, "username" => 'demo', "email" => 'demo@example.com', "password" => password_hash("demo", PASSWORD_BCRYPT), "state" => UserInterface::STATE_INACTIVE];
        $this->gateway = new Gateway([1 => $data], new UserMapper());
        $this->user = $this->gateway->getById(1);
        $this->tokenGen = new UserToken($this->gateway);
    }

    public function testInstanceOf()
    {
        $this->assertTrue($this->tokenGen instanceof TokenInterface);
    }

    public function testGenerateReturnsSameTokens()
    {
        $token1 = $this->tokenGen->generateToken($this->user);
        $token2 = $this->tokenGen->generateToken($this->user);
        $this->assertEquals($token1, $token2);
    }

    public function testCheckTokenReturnsTrueIfTokenIsSame()
    {
        $token = $this->tokenGen->generateToken($this->user);
        $this->assertEquals($this->user->getId(), $this->tokenGen->fetchToken($token));
    }

    public function testCheckTokenReturnsFalseIfTokenEmpty()
    {
        $this->assertFalse($this->tokenGen->fetchToken(""));
    }

    public function testCheckTokenReturnsFalseIfTokenWrong()
    {
        $token = $this->tokenGen->generateToken($this->user);
        $this->assertFalse($this->tokenGen->fetchToken($token . "a"));
    }
}
