<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\TokenGenerator\TokenGenerator;
use SugiPHP\Auth2\TokenGenerator\TokenGeneratorInterface;

class TokenGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $this->assertTrue(new TokenGenerator() instanceof TokenGeneratorInterface);
    }

    public function testCreateWithLengthChangesTokenLength()
    {
        $len = 100;
        $generator = new TokenGenerator();
        $generator->setTokenLenght($len);
        $this->assertEquals($len, $generator->getTokenLenght());
    }

    public function testGenerateReturnsRandomTokens()
    {
        $generator = new TokenGenerator();
        $token1 = $generator->generateToken();
        $token2 = $generator->generateToken();
        $this->assertNotEquals($token1, $token2);
    }

    public function testGenerateReturnsTokensWithTokenLength()
    {
        $generator = new TokenGenerator();
        $token = $generator->generateToken();
        $this->assertEquals($generator->getTokenLenght(), strlen($token));
    }

    public function testGenerateReturnsTokensWithSpecifiedTokenLength()
    {
        $len = 50;
        $generator = new TokenGenerator();
        $generator->setTokenLenght($len);
        $token = $generator->generateToken();
        $this->assertEquals($len, strlen($token));
    }
}
