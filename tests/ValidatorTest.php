<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Tests;

use SugiPHP\Auth2\Validator\Validator;
use SugiPHP\Auth2\Validator\ValidatorInterface;
use Psr\Log\NullLogger;
use SugiPHP\Auth2\Exception\InvalidArgumentException;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $this->assertTrue(new Validator() instanceof ValidatorInterface);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckEmailTrowsExceptionIfEmpty()
    {
        $validator = new Validator();
        $validator->checkEmail("");
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckEmailTrowsExceptionIfMoreThan255Chars()
    {
        $email = str_repeat("a", 244) . "@example.com"; // 256 chars
        $validator = new Validator();
        $validator->checkEmail($email);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckEmailTrowsExceptionIfNoValidMailGiven()
    {
        $email = "foobar#example.com";
        $validator = new Validator();
        $validator->checkEmail($email);
    }

    public function testCheckEmailDoesNotTrhowExceptionIfTheMailAddressIsLegal()
    {
        $email = "foobar@example.com";
        $validator = new Validator();
        $validator->checkEmail($email);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckUsernameEmpty()
    {
        $username = "";
        $validator = new Validator();
        $validator->checkUsername($username);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckUsernameTooShort()
    {
        $username = "ab";
        $validator = new Validator();
        $validator->checkUsername($username);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckUsernameTooLong()
    {
        $username = str_repeat("a", 33);
        $validator = new Validator();
        $validator->checkUsername($username);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckUsernameIllegalChars()
    {
        $username = "E=mc^2";
        $validator = new Validator();
        $validator->checkUsername($username);
    }

    public function testCheckUsernameIsOk()
    {
        $username = "demo";
        $validator = new Validator();
        $validator->checkUsername($username);
    }

    public function testCheckPasswordIsOk()
    {
        $password = "aB$ 2eF9--foo";
        $validator = new Validator();
        $validator->checkPassword($password);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckPasswordEmpty()
    {
        $password = "";
        $validator = new Validator();
        $validator->checkPassword($password);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckPasswordTooShort()
    {
        $password = "aB6&";
        $validator = new Validator();
        $validator->checkPassword($password);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckPasswordTooSimple()
    {
        $password = "1234567890";
        $validator = new Validator();
        $validator->checkPassword($password);
    }

    /**
     * @depends testCheckPasswordIsOk
     */
    public function testCheckPasswordConfirmation()
    {
        $password = "aB$ 2eF9--foo";
        $validator = new Validator();
        $validator->checkPasswordConfirmation($password, $password);
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckPasswordConfirmationMissing2()
    {
        $password = "aB$ 2eF9--foo";
        $validator = new Validator();
        $validator->checkPasswordConfirmation($password, "");
    }

    /**
     * @expectedException SugiPHP\Auth2\Exception\InvalidArgumentException
     */
    public function testCheckPasswordConfirmationMissing()
    {
        $password = "aB$ 2eF9--foo";
        $validator = new Validator();
        $validator->checkPasswordConfirmation($password."Q", $password);
    }
}
