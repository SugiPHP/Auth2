<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Gateway;

interface ActivationGatewayInterface extends LoginGatewayInterface
{
    /**
     * Changes user state.
     *
     * @param integer $id
     * @param integer $state
     */
    public function updateState($id, $state);
}
