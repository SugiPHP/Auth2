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

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $this->assertTrue(new Validator() instanceof ValidatorInterface);
    }

    public function testCheckEmailTrowsExceptionIfEmpty()
    {
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkEmail("");
    }

    public function testCheckEmailTrowsExceptionIfMoreThan255Chars()
    {
        $email = str_repeat("a", 244) . "@example.com"; // 256 chars
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkEmail($email);
    }

    public function testCheckEmailTrowsExceptionIfNoValidMailGiven()
    {
        $email = "foobar#example.com";
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkEmail($email);
    }

    public function testCheckEmailDoesNotTrhowExceptionIfTheMailAddressIsLegal()
    {
        $email = "foobar@example.com";
        $validator = new Validator();
        $validator->checkEmail($email);
    }

    public function testCheckUsernameEmpty()
    {
        $username = "";
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkUsername($username);
    }

    public function testCheckUsernameTooShort()
    {
        $username = "ab";
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkUsername($username);
    }

    public function testCheckUsernameTooLong()
    {
        $username = str_repeat("a", 33);
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkUsername($username);
    }

    public function testCheckUsernameIllegalChars()
    {
        $username = "E=mc^2";
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
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

    public function testCheckPasswordEmpty()
    {
        $password = "";
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkPassword($password);
    }

    public function testCheckPasswordTooShort()
    {
        $password = "aB6&";
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkPassword($password);
    }

    public function testCheckPasswordTooSimple()
    {
        $password = "1234567890";
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkPassword($password);
    }

    public function testCheckPasswordConfirmation()
    {
        $password = "aB$ 2eF9--foo";
        $validator = new Validator();
        $validator->checkPasswordConfirmation($password, $password);
    }

    public function testCheckPasswordConfirmationMissing2()
    {
        $password = "aB$ 2eF9--foo";
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkPasswordConfirmation($password, "");
    }

    public function testCheckPasswordConfirmationMissing()
    {
        $password = "aB$ 2eF9--foo";
        $validator = new Validator();
        $this->expectException(\SugiPHP\Auth2\Exception\InvalidArgumentException::class);
        $validator->checkPasswordConfirmation($password."Q", $password);
    }
}
