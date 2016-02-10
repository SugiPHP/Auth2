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
use SugiPHP\Auth2\Gateway\LoginGatewayInterface;
use SugiPHP\Auth2\Gateway\RegistrationGatewayInterface;

class MemoryGatewayTest extends \PHPUnit_Framework_TestCase
{
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com',      "password" => '', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "password" => '', "state" => 1],
        9 => ["id" => 9, "username" => 'bar',  "email" => null,               "password" => '', "state" => 2],
    ];

    private $gateway;

    public function setUp()
    {
        $this->gateway = new Gateway(self::DEMODATA, new UserMapper());
    }

    public function testGatewayImplementsLoginGatewayInterface()
    {
        $this->assertTrue($this->gateway instanceof LoginGatewayInterface);
    }

    public function testGatewayImplementsRegistrationGatewayInterface()
    {
        $this->assertTrue($this->gateway instanceof RegistrationGatewayInterface);
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetById()
    {
        $user = $this->gateway->getById(7);
        $this->assertEquals(7, $user->getId());
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByIdReturnsData()
    {
        $user = $this->gateway->getById(7);
        $this->assertEquals(7, $user->getId());
        $this->assertEquals('demo', $user->getUsername());
        $this->assertEquals('demo@example.com', $user->getEmail());
        $this->assertEquals(1, $user->getState());
        $p = $user->getPassword();
        $this->assertTrue(isset($p));
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByIdReturnsEmptyIfUserNotFound()
    {
        $user = $this->gateway->getById(99);
        $this->assertEmpty($user);
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByUsername()
    {
        $user = $this->gateway->getByUsername("demo");
        $this->assertEquals(7, $user->getId());
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByUsernameReturnsEmptyIfUserNotFound()
    {
        $user = $this->gateway->getByUsername("nosuchuser");
        $this->assertEmpty($user);
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByEmail()
    {
        $user = $this->gateway->getByEmail("demo@example.com");
        $this->assertEquals(7, $user->getId());
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByEmailReturnsEmptyIfUserNotFound()
    {
        $user = $this->gateway->getByUsername("no-reply@example.com");
        $this->assertEmpty($user);
    }

    /**
     * @depends testGatewayImplementsRegistrationGatewayInterface
     */
    public function testAddReturnsUser()
    {
        $user = $this->gateway->add("new@user.mail", "newusername", 2, "");
        $this->assertTrue($user instanceof UserInterface);
    }

    /**
     * @depends testGatewayImplementsRegistrationGatewayInterface
     * @depends testAddReturnsUser
     */
    public function testAddInsertsProperData()
    {
        $user = $this->gateway->add("new@user.mail", "newusername", 2, "");
        $this->assertGreaterThan(0, $user->getId());
        $this->assertEquals("new@user.mail", $user->getEmail());
        $this->assertEquals("newusername", $user->getUsername());
        $this->assertEquals(2, $user->getState());
    }

    /**
     * @depends testGatewayImplementsRegistrationGatewayInterface
     */
    public function testUpdateState()
    {
        $user = $this->gateway->getById(7);
        $oldstate = $user->getState();

        $res = $this->gateway->updateState(7, $oldstate + 1);
        $this->assertTrue($res);
        $user = $this->gateway->getById(7);
        $newstate = $user->getState();
        $this->assertEquals($oldstate + 1, $newstate);
    }

    public function testUpdateStateReturnsFalse()
    {
        $res = $this->gateway->updateState(999, 1);
        $this->assertFalse($res);
    }

    public function testUpdatePassword()
    {
        $user = $this->gateway->getById(7);
        $oldPass = $user->getPassword();

        $res = $this->gateway->updatePassword(7, "newhash");
        $this->assertTrue($res);
        $user = $this->gateway->getById(7);
        $newPass = $user->getPassword();
        $this->assertNotEquals($newPass, $oldPass);
    }

    public function testUpdatePasswordReturnsFalse()
    {
        $res = $this->gateway->updatePassword(999, "newhash");
        $this->assertFalse($res);
    }

    public function testFindTokenReturnsFalseIfTokenNotFound()
    {
        $token = md5(mt_rand());
        $userId = $this->gateway->findToken($token);
        $this->assertFalse($userId);
    }

    public function testFindTokenReturnsFalseIfTokenEmpty()
    {
        $token = "";
        $userId = $this->gateway->findToken($token);
        $this->assertFalse($userId);
    }

    public function testStoreToken()
    {
        $userId = 7;
        $token = md5(mt_rand());
        $this->gateway->storeToken($token, $userId);
        $this->assertEquals($userId, $this->gateway->findToken($token));
    }

    /**
     * @depends testStoreToken
     */
    public function testStoreMoreTokens()
    {
        $user1 = 1;
        $token1 = md5(mt_rand());
        $token1more = md5(mt_rand());
        $user7 = 7;
        $token7 = md5(mt_rand());
        $this->gateway->storeToken($token1, $user1);
        $this->gateway->storeToken($token7, $user7);
        $this->gateway->storeToken($token1more, $user1);
        $this->assertEquals($user1, $this->gateway->findToken($token1));
        $this->assertEquals($user7, $this->gateway->findToken($token7));
        $this->assertEquals($user1, $this->gateway->findToken($token1more));
    }

    /**
     * @depends testStoreMoreTokens
     */
    public function testDeleteUserTokens()
    {
        $user1 = 1;
        $token1 = md5(mt_rand());
        $token1more = md5(mt_rand());
        $user7 = 7;
        $token7 = md5(mt_rand());
        $this->gateway->storeToken($token1, $user1);
        $this->gateway->storeToken($token7, $user7);
        $this->gateway->storeToken($token1more, $user1);

        $this->gateway->deleteUserTokens($user1);

        $this->assertEmpty($this->gateway->findToken($token1));
        $this->assertEmpty($this->gateway->findToken($token1more));
        $this->assertEquals($user7, $this->gateway->findToken($token7));
    }

    /**
     * @depends testStoreToken
     * @depends testFindTokenReturnsFalseIfTokenNotFound
     */
    public function testDeleteToken()
    {
        $userId = 7;
        $token = md5(mt_rand());
        $this->gateway->storeToken($token, $userId);
        $this->gateway->deleteToken($token);
        $this->assertFalse($this->gateway->findToken($token));
    }
}
