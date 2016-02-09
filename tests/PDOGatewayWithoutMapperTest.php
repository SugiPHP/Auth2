<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\Gateway\PDOGateway as Gateway;
use SugiPHP\Auth2\Gateway\LoginGatewayInterface;
use SugiPHP\Auth2\Gateway\RegistrationGatewayInterface;
use PDO;

class PDOGatewayWithoutMapperTest extends \PHPUnit_Framework_TestCase
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
        $this->assertNotEmpty($row["reg_date"]);
        $this->assertNotEmpty($row["pass_change_date"]);
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
    public function testAddReturnsArray()
    {
        $arr = $this->gateway->add("new@user.mail", "newusername", 2, "");
        $this->assertTrue(is_array($arr));
    }

    /**
     * @depends testGatewayImplementsRegistrationGatewayInterface
     * @depends testAddReturnsArray
     */
    public function testAddInsertsProperData()
    {
        $user = $this->gateway->add("new@user.mail", "newusername", 2, "");
        $id = $user["id"];
        $row = $this->gateway->getById($id);
        $this->assertEquals($id, $row["id"]);
        $this->assertEquals("new@user.mail", $row["email"]);
        $this->assertEquals("newusername", $row["username"]);
        $this->assertEquals(2, $row["state"]);
        $this->assertNotEmpty($row["reg_date"]);
        $this->assertNotEmpty($row["pass_change_date"]);
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

    public function testUpdatePassword()
    {
        $user = $this->gateway->getById(7);
        $oldPass = $user["password"];

        $res = $this->gateway->updatePassword(7, "newhash");
        $this->assertTrue($res);
        $user = $this->gateway->getById(7);
        $newPass = $user["password"];
        $this->assertNotEquals($newPass, $oldPass);
    }

    public function testUpdatePasswordReturnsFalse()
    {
        $res = $this->gateway->updatePassword(999, "newhash");
        $this->assertFalse($res);
    }
}
