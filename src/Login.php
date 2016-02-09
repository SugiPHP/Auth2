<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2;

use SugiPHP\Auth2\Gateway\LoginGatewayInterface;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\Exception\InvalidArgumentException;
use SugiPHP\Auth2\User\UserInterface;
use Plana\Domain\Auth\Storage\StorageInterface;
use Psr\Log\LoggerAwareTrait;
use UnexpectedValueException;

class Login
{
    use PasswordHashTrait;
    use LoggerAwareTrait;

    /**
     * @var Instance of Gateway\LoginGatewayInterface
     */
    private $gateway;

    /**
     * @var Instance of Storage\StorageInterface
     */
    private $storage;

    /**
     * Checked user.
     *
     * @var User\UserInterface
     */
    private $user;

    public function __construct(LoginGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Login
     *
     * @param string $login Username or email
     * @param string $password
     *
     * @throws InvalidArgumentException If the username/password is not given
     * @throws GeneralException On login fail
     * @throws GeneralException On not activated (inactive/blocked) accounts
     */
    public function login($login, $password)
    {
        // Incorrect username or password
        // The email and password you entered don't match
        // The password you entered for the email or username demo is incorrect
        // The username and password you entered did not match our records. Please double-check and try again
        // Username/password mismatch
        // Email/password mismatch
        // Грешно потребителско име/парола
        // Грешен email адрес/парола
        // Грешен потребител/парола
        $loginFailedMessage = "Incorrect username or password";

        // check username or email is set
        if (!$login) {
            // exception code 1 for the 1st argument
            throw new InvalidArgumentException("Моля въведете потребител", 1);
        }

        // checks password is set
        if (!$password) {
            throw new InvalidArgumentException("Моля въведете парола", 2);
        }

        if ($emailLogin = (strpos($login, "@") > 0)) {
            if (!$user = $this->gateway->getByEmail($login)) {
                throw new GeneralException($loginFailedMessage);
            }
        } else {
            if (!$user = $this->gateway->getByUsername($login)) {
                throw new GeneralException($loginFailedMessage);
            }
        }

        $this->checkState($user->getState());

        // check password
        if (!$this->checkSecret($password, $user->getPassword())) {
            throw new GeneralException($loginFailedMessage);
        }

        if ($this->storage) {
            $this->storage->set($user);
        }
        $this->user = $user;

        // Removing password from the return
        return $user->withPassword(null);
    }

    public function logout()
    {
        if ($this->storage) {
            $this->storage->remove();
        }
        $this->user = null;
    }

    public function getUser()
    {
        if ($this->user) {
            return $this->user;
        }

        if (!$user = $this->storage->get()) {
            return ;
        }

        if (!$data = $this->gateway->getById($user->getId())) {
            // clear stored user
            $this->storage->remove();
            return ;
        }

        // check after login if the user has been blocked
        if (UserInterface::STATE_ACTIVE != $data->getState()) {
            // clear stored user
            $this->storage->remove();
            return ;
        }

        // check if the user has changed his/her password (probably on some other device),
        // so force him/her to authenticate again.
        if ($data->getPassword() != $user->getPassword()) {
            // clear stored user
            $this->storage->remove();
            return ;
        }

        // Removing password from the return
        return $data->withPassword(null);
    }

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Checks current user state and throws exception if it is not active
     */
    private function checkState($state)
    {
        if (UserInterface::STATE_INACTIVE == $state) {
            // Before login you have to confirm your email address
            throw new GeneralException("Моля потвърдете регистрацията си");
        }

        if (UserInterface::STATE_BLOCKED == $state) {
            // User account is blocked
            throw new GeneralException("Вашият потребителски акаунт е блокиран");
        }

        if (UserInterface::STATE_ACTIVE != $state) {
            throw new UnexpectedValueException("Unknown user state. Expected 1-3. Got {$state}");
        }
    }
}
