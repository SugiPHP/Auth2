<?php
/**
 * @package SugiPHP.Auth2
 * @author  Ivan Hidzhov <ivan.hidzhov@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\Storage\NativeSessionStorage;
use InvalidArgumentException;

class NativeSessionStorageTest extends \PHPUnit\Framework\TestCase
{
    public function testSetSessionKey()
    {
        $storageSession = new NativeSessionStorage();
        $sessionKey = $storageSession->getSessionKey();
        $storageSession->setSessionKey($sessionKey."!");
        $this->assertEquals($sessionKey."!", $storageSession->getSessionKey());
    }

    /**
     * @depends testSetSessionKey
     */
    public function testSetAndGet()
    {
        // Equals
        $storageSession = new NativeSessionStorage();
        $storageSession->set(['boo' => 'foo']);
        $this->assertEquals(['boo' => 'foo'], $storageSession->get());

        // Not equals
        $storageSession->setSessionKey('other_key');
        $this->assertNotEquals(['boo' => 'foo'], $storageSession->get());
    }

    public function testRemove()
    {
        $storageSession = new NativeSessionStorage();
        $storageSession->set(['boo' => 'foo']);
        $this->assertEquals(['boo' => 'foo'], $storageSession->get());
        // Remove all data
        $storageSession->remove();
        $this->assertNotEquals(['boo' => 'foo'], $storageSession->get());
    }

    public function testHas()
    {
        $storageSession = new NativeSessionStorage();
        $this->assertFalse($storageSession->has());
        // Need to set some data to has...
        $storageSession->set('boo');
        $this->assertTrue($storageSession->has());
    }
}
