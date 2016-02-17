<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\User\UserMapper;
use SugiPHP\Auth2\User\UserInterface;
use SugiPHP\Auth2\Gateway\PDOGateway as Gateway;
use SugiPHP\Auth2\Gateway\LoginGatewayInterface;
use SugiPHP\Auth2\Gateway\RegistrationGatewayInterface;
use PDO;

class PDOGatewayTest extends \PHPUnit_Framework_TestCase
{
    const SCHEMA = "CREATE TABLE auth2 (
        id INTEGER NOT NULL PRIMARY KEY,
        username VARCHAR(255) UNIQUE,
        email VARCHAR(255) UNIQUE,
        password VARCHAR(255),
        state INTEGER NOT NULL,
        reg_date TIMESTAMP NOT NULL,
        pass_change_date TIMESTAMP)";
    const DEMODATA = "INSERT INTO auth2 VALUES
        (1, 'foo', 'foo@bar.com', '', 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (7, 'demo', 'demo@example.com', '', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (9, 'bar', NULL, NULL, 2, CURRENT_TIMESTAMP, NULL)";

    private $gateway;

    public function setUp()
    {
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec(self::SCHEMA);
        $db->exec(self::DEMODATA);

        $this->gateway = new Gateway($db);
        $this->gateway->setUserMapper(new UserMapper());
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
        // $this->assertNotEmpty($user["reg_date"]);
        // $this->assertNotEmpty($user["pass_change_date"]);
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
        // $this->assertNotEmpty($user["reg_date"]);
        // $this->assertNotEmpty($user["pass_change_date"]);
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
}
