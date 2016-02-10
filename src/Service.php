<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2;

/**
 * Service Locator
 */
class Service
{
    /**
     * @var User\MapperInterface
     */
    private $userMapper;

    /**
     * @var Login
     */
    private $loginService;

    /**
     * @var Registration
     */
    private $registrationService;

    /**
     * @var PasswordService
     */
    private $passwordService;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var PDO
     */
    private $db;

    /**
     * @var Token\TokenInterface
     */
    private $tokenGenerator;

    /**
     * @var Validator\ValidatorInterface
     */
    private $validator;

    /**
     * @var Gateway\LoginGatewayInterface
     */
    private $loginGateway;

    /**
     * @var Gateway\RegistartionGatewayInterface
     */
    private $registrationGateway;

    /**
     * @var Gateway\PasswordGatewayInterface
     */
    private $passwordGateway;

    /**
     * This instance will be returned if (Login|Registration|Password)Gateway are not set
     * @var Instance of PDOGateway or MemoryGateway
     */
    private $gateway;

    /**
     * @var Storage\StorageInterface
     */
    private $storage;

    public function __construct()
    {
        $this->args = func_get_args();

        $this->storage = $this->findImplementation("Storage\\StorageInterface");
        $this->db = $this->findImplementation("\\PDO");
        $this->logger = $this->findImplementation("\\Psr\\Log\\LoggerInterface");
        $this->validator = $this->findImplementation("Validator\\ValidatorInterface");
        $this->userMapper = $this->findImplementation("User\\MapperInterface");
        $this->tokenGenerator = $this->findImplementation("Token\\TokenInterface");
        $this->loginGateway = $this->findImplementation("Gateway\\LoginGatewayInterface");
        $this->registrationGateway = $this->findImplementation("Gateway\\RegistrationGatewayInterface");
        $this->passwordGateway = $this->findImplementation("Gateway\\PasswordGatewayInterface");
    }

    public function getUser()
    {
        return $this->getLoginService()->getUser();
    }

    public function login($login, $password)
    {
        return $this->getLoginService()->login($login, $password);
    }

    public function logout()
    {
        return $this->getLoginService()->logout();
    }

    public function register($email, $username, $password, $password2)
    {
        return $this->getRegistrationService()->register($email, $username, $password, $password2);
    }

    public function activate($login, $token)
    {
        return $this->getRegistrationService()->activate($login, $token);
    }

    public function forgotPassword($email)
    {
        return $this->getPasswordService()->genToken($email);
    }

    public function resetPassword($login, $token, $password1, $password2)
    {
        return $this->getPasswordService()->resetPassword($login, $token, $password1, $password2);
    }

    public function changePassword($userId, $old, $password1, $password2)
    {
        return $this->getPasswordService()->changePassword($userId, $old, $password1, $password2);
    }

    public function getStorage()
    {
        if (!$this->storage) {
            $this->storage = new Storage\NativeSessionStorage();
        }

        return $this->storage;
    }

    public function getLoginService()
    {
        if (!$this->loginService) {
            $this->loginService = new Login($this->getLoginGateway());
            if ($this->logger) {
                $this->loginService->setLogger($this->logger);
            }
            // this might be better to be set only if the storage is instantiated already!
            $this->loginService->setStorage($this->getStorage());
        }

        return $this->loginService;
    }

    public function getRegistrationService()
    {
        if (!$this->registrationService) {
            $this->registrationService = new Registration($this->getRegistrationGateway(), $this->getTokenGenerator(), $this->getValidator());
            if ($this->logger) {
                $this->registrationService->setLogger($this->logger);
            }
            // this might be better to be set only if the storage is instantiated already!
            $this->registrationService->setStorage($this->getStorage());
        }

        return $this->registrationService;
    }

    public function getPasswordService()
    {
        if (!$this->passwordService) {
            $this->passwordService = new PasswordService($this->getPasswordGateway(), $this->getTokenGenerator(), $this->getValidator());
            if ($this->logger) {
                $this->passwordService->setLogger($this->logger);
            }
            // this might be better to be set only if the storage is instantiated already!
            $this->passwordService->setStorage($this->getStorage());
        }

        return $this->passwordService;
    }

    public function getLoginGateway()
    {
        if (!$this->loginGateway) {
            $this->loginGateway = $this->getGateway();
        }

        return $this->loginGateway;
    }

    public function getRegistrationGateway()
    {
        if (!$this->registrationGateway) {
            $this->registrationGateway = $this->getGateway();
        }

        return $this->registrationGateway;
    }

    public function getPasswordGateway()
    {
        if (!$this->registrationGateway) {
            $this->passwordGateway = $this->getGateway();
        }

        return $this->passwordGateway;
    }

    public function getValidator()
    {
        if (!$this->validator) {
            $this->validator = new Validator\Validator();
            if ($this->logger) {
                $this->validator->setLogger($this->logger);
            }
        }

        return $this->validator;
    }

    public function getUserMapper()
    {
        if (!$this->userMapper) {
            $this->userMapper = new User\UserMapper();
        }

        return $this->userMapper;
    }

    public function getTokenGenerator()
    {
        if (!$this->tokenGenerator) {
            $this->tokenGenerator = new Token\UserToken();
        }

        return $this->tokenGenerator;
    }

    private function getGateway()
    {
        if (!$this->gateway) {
            if ($this->db) {
                $this->gateway = new Gateway\PDOGateway($this->db, $this->getUserMapper());
            } else {
                $this->gateway = new Gateway\MemoryGateway([], $this->getUserMapper());
            }
        }

        return $this->gateway;
    }

    private function findImplementation($interface)
    {
        if (strpos($interface, "\\") !== 0) {
            $interface = __NAMESPACE__."\\".$interface;
        }
        foreach ($this->args as $class) {
            if ($class instanceof $interface) {
                return $class;
            }
        }
    }
}
