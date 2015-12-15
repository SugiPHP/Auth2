<?php
/**
 * @package  SugiPHP.Auth2
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\Validator\Validator;
use SugiPHP\Auth2\Validator\ValidatorInterface;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateSuccessfull()
    {
        new Validator();
    }
}
