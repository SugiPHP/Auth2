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
}
