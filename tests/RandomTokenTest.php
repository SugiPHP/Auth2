<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\Token\RandomToken;
use SugiPHP\Auth2\Token\TokenInterface;
use SugiPHP\Auth2\Gateway\MemoryGateway as Gateway;
use SugiPHP\Auth2\User\UserMapper;
use SugiPHP\Auth2\User\UserInterface;
use SugiPHP\Auth2\User\User;

class RandomTokenTest extends \PHPUnit\Framework\TestCase
{
    private $tokenGen;
    private $user;

    public function setUp(): void
    {
        $this->user = new User(1, "demo", "demo@example.com", UserInterface::STATE_INACTIVE, password_hash("demo", PASSWORD_BCRYPT));
        $gateway = new Gateway([]);
        $gateway->setUserMapper(new UserMapper());
        $this->tokenGen = new RandomToken($gateway);
    }

    public function testCreate()
    {
        $this->assertTrue($this->tokenGen instanceof TokenInterface);
    }

    public function testCreateWithLengthChangesTokenLength()
    {
        $len = 100;
        $this->tokenGen->setTokenLength($len);
        $this->assertEquals($len, $this->tokenGen->getTokenLength());
    }

    public function testGenerateReturnsRandomTokens()
    {
        $token1 = $this->tokenGen->generateToken($this->user->getId());
        $token2 = $this->tokenGen->generateToken($this->user->getId());
        $this->assertNotEquals($token1, $token2);
    }

    public function testGenerateReturnsTokensWithTokenLength()
    {
        $token = $this->tokenGen->generateToken($this->user->getId());
        $this->assertEquals($this->tokenGen->getTokenLength(), strlen($token));
    }

    public function testGenerateReturnsTokensWithSpecifiedTokenLength()
    {
        $len = 50;
        $this->tokenGen->setTokenLength($len);
        $token = $this->tokenGen->generateToken($this->user->getId());
        $this->assertEquals($len, strlen($token));
    }

    public function testFetchTokenReturnsTrueIfTokenIsSame()
    {
        $token = $this->tokenGen->generateToken($this->user->getId());
        $this->assertEquals($this->user->getId(), $this->tokenGen->fetchToken($token));
    }

    public function testFetchTokenReturnsFalseIfTokenEmpty()
    {
        $this->assertFalse($this->tokenGen->fetchToken(""));
    }

    public function testFetchTokenReturnsFalseIfTokenWrong()
    {
        $token = $this->tokenGen->generateToken($this->user->getId());
        $this->assertFalse($this->tokenGen->fetchToken($token . "a"));
    }

    /**
     * @depends testFetchTokenReturnsTrueIfTokenIsSame
     */
    public function testInvalidateToken()
    {
        $token = $this->tokenGen->generateToken($this->user->getId());
        $this->tokenGen->invalidateToken($token);
        $this->assertFalse($this->tokenGen->fetchToken($token));
    }
}
