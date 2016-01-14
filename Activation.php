<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2;

use SugiPHP\Auth2\Gateway\ActivationGatewayInterface as ActivationGateway;
use SugiPHP\Auth2\Token\TokenInterface;
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

    /**
     * @var Instance of TokenInterface
     */
    private $tokenGen;

    public function __construct(ActivationGateway $gateway, TokenInterface $tokenGen)
    {
        $this->gateway = $gateway;
        $this->tokenGen = $tokenGen;
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
            // If the state is already active it can be insecure to use
            // the token for logins. The token might be old if it is not
            // invalidated, or it is based on user's data (e.g. UserToken)
            // So we SHOULD return true, instead of User Data
            return true;
        }

        if (!$this->checkUserToken($user, $token)) {
            throw new GeneralException("Wrong activation token");
        }

        if (!$this->gateway->updateState($user->getId(), UserInterface::STATE_ACTIVE)) {
            throw new GeneralException("Error in activation process");
        }

        $this->invalidateUserToken($user, $token);

        return $user;
    }

    /**
     * Checks current user state and throws exception if it is blocked
     *
     * @param boolean $state
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
     * Checks the user can use this token
     *
     * @param UserInterface $user
     * @param string $token
     *
     * @return boolean
     */
    private function checkUserToken(UserInterface $user, $token)
    {
        return $this->tokenGen->checkToken($user, $token);
    }

    private function invalidateUserToken(UserInterface $user, $token)
    {
        return $this->tokenGen->invalidateToken($user, $token);
    }
}
