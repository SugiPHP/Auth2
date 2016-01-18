<?php
/**
 * @package SugiPHP.Auth2
 * @author  Ivan Hidzhov <ivan.hidzhov@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\Storage\MemoryStorage;
use InvalidArgumentException;

class MemoryStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testSetAndGet()
    {
        // Equals
        $memoryStore = new MemoryStorage();
        $memoryStore->set(['boo' => 'foo']);
        $this->assertEquals(['boo' => 'foo'], $memoryStore->get());
    }

    public function testRemove()
    {
        $memoryStore = new MemoryStorage();
        $memoryStore->set(['boo' => 'foo']);
        $this->assertEquals(['boo' => 'foo'], $memoryStore->get());
        // Remove all data
        $memoryStore->remove();
        $this->assertNotEquals(['boo' => 'foo'], $memoryStore->get());
    }

    public function testHas()
    {
        $memoryStore = new MemoryStorage();
        $this->assertFalse($memoryStore->has());
        // Need to set some data to has...
        $memoryStore->set('boo');
        $this->assertTrue($memoryStore->has());
    }
}
