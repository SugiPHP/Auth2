<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\User\User;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\Exception\InvalidArgumentException;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testUserCreation()
    {
        $user = new User(2, 'demo', 'demo@example.com', 1, 'passw0rd');
        $this->assertEquals("demo", $user->getUsername());
        $this->assertEquals("demo@example.com", $user->getEmail());
        $this->assertEquals("passw0rd", $user->getPassword());
        $this->assertEquals(1, $user->getState());
        $this->assertEquals(2, $user->getId());
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testUserWithoutIdThrowsException()
    {
        new User(null, 'demo', 'demo@example.com', 1, 'passw0rd');
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testUserWithoutIdThrowsException2()
    {
        new User(false, 'demo', 'demo@example.com', 1, 'passw0rd');
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testUserWithId0ThrowsException()
    {
        new User(0, 'demo', 'demo@example.com', 1, 'passw0rd');
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testUserWithoutUsernameThrowsException()
    {
        new User(2, null, 'demo@example.com', 1, 'passw0rd');
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testUserWithEmptyUsernameThrowsException()
    {
        new User(2, '', 'demo@example.com', 1, 'passw0rd');
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testUserWithWrongState()
    {
        new User(2, 'demo', 'demo@example.com', -199, 'passw0rd');
    }

    public function testSetPassword()
    {
        $newPassword = "newpassword";
        $user = new User(2, 'demo', 'demo@example.com', 1, 'passw0rd');
        $passwordHash1 = $user->getPassword();
        $user->setPassword($newPassword);
        $this->assertNotEquals($passwordHash1, $user->getPassword());
    }

    public function testSetPasswordCryptsPassword()
    {
        $newPassword = "newpassword";
        $user = new User(2, 'demo', 'demo@example.com', 1, 'passw0rd');
        $user->setPassword($newPassword);
        $this->assertNotEquals($newPassword, $user->getPassword());
    }

    public function testWithToken()
    {
        $user = new User(2, 'demo', 'demo@example.com', 1, 'passw0rd');
        $this->assertEmpty($user->getToken());
        $user2 = $user->withToken("randomToken");
        $this->assertTrue($user2 instanceof \SugiPHP\Auth2\User\UserInterface);
        $this->assertNotEquals($user, $user2);
        $this->assertEquals("randomToken", $user2->getToken());
    }

    public function testWithState()
    {
        $user = new User(2, 'demo', 'demo@example.com', 1, 'passw0rd');
        $this->assertEquals(1, $user->getState());
        $user2 = $user->withState(2);
        $this->assertTrue($user2 instanceof \SugiPHP\Auth2\User\UserInterface);
        $this->assertNotEquals($user, $user2);
        $this->assertEquals(2, $user2->getState());
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testWithStateTrhowsExceptionIfTheValueIsNotKnown()
    {
        $user = new User(2, 'demo', 'demo@example.com', 1, 'passw0rd');
        $user2 = $user->withState(99);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testWithStateTrhowsExceptionIfTheValueIsNotSet()
    {
        $user = new User(2, 'demo', 'demo@example.com', 1, 'passw0rd');
        $user2 = $user->withState(false);
    }
}
