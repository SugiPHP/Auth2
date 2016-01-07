<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2;

use SugiPHP\Auth2\Gateway\ActivationGatewayInterface as ActivationGateway;
use SugiPHP\Auth2\Exception\GeneralException;
use SugiPHP\Auth2\User\UserInterface;
use InvalidArgumentException;
use UnexpectedValueException;

class Activation
{
    /**
     * @var Instance of ActivationGatewayInterface
     */
    private $gateway;

    public function __construct(ActivationGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Activates user account
     *
     * @throws InvalidArgumentException if user/email or token is missing
     * @throws GeneralException if user is unknown
     * @throws GeneralException if user is blocked
     * @throws GeneralException token is wrong
     */
    public function activate($login, $token)
    {
        // check username or email is set
        if (!$login) {
            // exception code 1 for the 1st argument
            throw new InvalidArgumentException("Missing user parameter", 1);
        }

        // checks token is set
        if (!$token) {
            throw new InvalidArgumentException("Missing token", 2);
        }

        if ($emailLogin = (strpos($login, "@") > 0)) {
            if (!$user = $this->gateway->getByEmail($login)) {
                throw new GeneralException("Unknown user");
            }
        } else {
            if (!$user = $this->gateway->getByUsername($login)) {
                throw new GeneralException("Unknown user");
            }
        }

        // the user is already active
        if ($this->checkState($user->getState())) {
            return true;
        }

        if (!$this->checkToken($token)) {
            throw new GeneralException("Wrong activation token");
        }

        return $this->gateway->updateState($user->getId(), UserInterface::STATE_ACTIVE);
    }

    /**
     * Checks current user state and throws exception if it is blocked
     *
     * @param boolean
     * @throws GeneralException if user is blocked
     */
    private function checkState($state)
    {
        if (UserInterface::STATE_BLOCKED == $state) {
            // User account is blocked
            throw new GeneralException("Вашият потребителски акаунт е блокиран");
        }

        if (UserInterface::STATE_ACTIVE == $state) {
            return true;
        }

        if (UserInterface::STATE_INACTIVE != $state) {
            throw new UnexpectedValueException("Unknown user state. Expected 1-3. Got {$state}");
        }

        return false;
    }

    /**
     * @todo implement the method
     *
     * @param string $token
     *
     * @return boolean
     */
    private function checkToken($token)
    {
        return false;
    }
}