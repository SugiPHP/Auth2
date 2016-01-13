<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\Gateway\MemoryGateway as Gateway;
use SugiPHP\Auth2\Gateway\LoginGatewayInterface;
use SugiPHP\Auth2\Gateway\RegistrationGatewayInterface;

class MemoryGatewayWithoutMapperTest extends \PHPUnit_Framework_TestCase
{
    const DEMODATA = [
        1 => ["id" => 1, "username" => 'foo',  "email" => 'foo@bar.com',      "password" => '', "state" => 2],
        7 => ["id" => 7, "username" => 'demo', "email" => 'demo@example.com', "password" => '', "state" => 1],
        9 => ["id" => 9, "username" => 'bar',  "email" => null,               "password" => '', "state" => 2],
    ];

    private $gateway;

    public function setUp()
    {
        $this->gateway = new Gateway(self::DEMODATA);
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
        $row = $this->gateway->getById(7);
        $this->assertEquals(7, $row["id"]);
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByIdReturnsData()
    {
        $row = $this->gateway->getById(7);
        $this->assertEquals(7, $row["id"]);
        $this->assertEquals('demo', $row["username"]);
        $this->assertEquals('demo@example.com', $row["email"]);
        $this->assertEquals(1, $row["state"]);
        $this->assertTrue(isset($row["password"]));
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByIdReturnsEmptyIfUserNotFound()
    {
        $row = $this->gateway->getById(99);
        $this->assertEmpty($row);
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByUsername()
    {
        $row = $this->gateway->getByUsername("demo");
        $this->assertEquals(7, $row["id"]);
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByUsernameReturnsEmptyIfUserNotFound()
    {
        $row = $this->gateway->getByUsername("nosuchuser");
        $this->assertEmpty($row);
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByEmail()
    {
        $row = $this->gateway->getByEmail("demo@example.com");
        $this->assertEquals(7, $row["id"]);
    }

    /**
     * @depends testGatewayImplementsLoginGatewayInterface
     */
    public function testGetByEmailReturnsEmptyIfUserNotFound()
    {
        $row = $this->gateway->getByUsername("no-reply@example.com");
        $this->assertEmpty($row);
    }

    /**
     * @depends testGatewayImplementsRegistrationGatewayInterface
     */
    public function testAddReturnsId()
    {
        $id = $this->gateway->add("new@user.mail", "newusername", 2, "");
        $this->assertGreaterThan(1, $id);
    }

    /**
     * @depends testGatewayImplementsRegistrationGatewayInterface
     * @depends testAddReturnsId
     */
    public function testAddInsertsProperData()
    {
        $id = $this->gateway->add("new@user.mail", "newusername", 2, "");
        $row = $this->gateway->getById($id);
        $this->assertEquals($id, $row["id"]);
        $this->assertEquals("new@user.mail", $row["email"]);
        $this->assertEquals("newusername", $row["username"]);
        $this->assertEquals(2, $row["state"]);
    }

    /**
     * @depends testGatewayImplementsRegistrationGatewayInterface
     */
    public function testUpdateState()
    {
        $row = $this->gateway->getById(7);
        $oldstate = $row["state"];

        $this->gateway->updateState(7, $oldstate + 1);
        $row = $this->gateway->getById(7);
        $newstate = $row["state"];
        $this->assertEquals($oldstate + 1, $newstate);
    }


    public function testFindTokenReturnsFalseIfTokenNotFound()
    {
        $token = md5(mt_rand());
        $user = $this->gateway->findToken($token);
        $this->assertFalse($user);
    }

    public function testFindTokenReturnsFalseIfTokenEmpty()
    {
        $token = "";
        $user = $this->gateway->findToken($token);
        $this->assertFalse($user);
    }

    public function testStoreToken()
    {
        $user = $this->gateway->getById(7);
        $token = md5(mt_rand());
        $this->gateway->storeToken($token, $user);
        $user2 = $this->gateway->findToken($token);
        $this->assertEquals($user, $user2);
    }

    public function testStoreMoreTokens()
    {
        $user1 = $this->gateway->getById(1);
        $token1 = md5(mt_rand());
        $token1more = md5(mt_rand());
        $user7 = $this->gateway->getById(7);
        $token7 = md5(mt_rand());
        $this->gateway->storeToken($token1, $user1);
        $this->gateway->storeToken($token7, $user7);
        $this->gateway->storeToken($token1more, $user1);
        $this->assertEquals($user1, $this->gateway->findToken($token1));
        $this->assertEquals($user7, $this->gateway->findToken($token7));
        $this->assertEquals($user1, $this->gateway->findToken($token1more));
    }
}
