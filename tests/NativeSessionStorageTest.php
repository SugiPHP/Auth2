<?php
/**
 * @package SugiPHP.Auth2
 * @author  Ivan Hidzhov <ivan.hidzhov@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\Storage\NativeSessionStorage;
use InvalidArgumentException;

class NativeSessionStorageTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        @session_start();   
    }

    public function testSetAndGet()
    {
        // Equals
        $storageSession = new NativeSessionStorage();
        $storageSession->set(['boo' => 'foo']);
        $this->assertEquals(['boo' => 'foo'], $storageSession->get());

        // Not equals
        $storageSession = new NativeSessionStorage('other_key');
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
        $storageSession = new NativeSessionStorage('key');
        // Need to set some data to has...
        $storageSession->set(['boo' => 'foo']);
        $this->assertTrue($storageSession->has());
    }

}
