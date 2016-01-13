<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\User\User;
use SugiPHP\Auth2\User\UserInterface;
use SugiPHP\Auth2\Token\UserToken;
use SugiPHP\Auth2\Token\TokenInterface;

class UserTokenTest extends \PHPUnit_Framework_TestCase
{
    private $tokenGen;
    private $user;

    public function setUp()
    {
        $this->user = new User(1, "demo", "demo@example.com", UserInterface::STATE_INACTIVE, password_hash("demo", PASSWORD_BCRYPT));
        $this->tokenGen = new UserToken();
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
        $this->assertTrue($this->tokenGen->checkToken($this->user, $token));
    }

    public function testCheckTokenReturnsFalseIfTokenEmpty()
    {
        $this->assertFalse($this->tokenGen->checkToken($this->user, ""));
    }

    public function testCheckTokenReturnsFalseIfTokenWrong()
    {
        $token = $this->tokenGen->generateToken($this->user);
        $this->assertFalse($this->tokenGen->checkToken($this->user, $token . "*"));
    }
}
