<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */
declare(strict_types=1);

namespace Centreon\Application\Controller;

use Centreon\Domain\Security\Interfaces\AuthenticationServiceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @package Centreon\Application\Controller
 */
class AuthenticationController extends AbstractFOSRestController
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $auth;

    /**
     * LoginController constructor.
     *
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Entry point used to identify yourself and retrieve an authentication token.
     * (If view_response_listener = true, we need to write the following
     * annotation Rest\View(populateDefaultVars=false), otherwise it's not
     * necessary).
     *
     * @Rest\Post("/login")
     * @Rest\View(populateDefaultVars=false)
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function login(Request $request)
    {
        try {
            // We take this opportunity to delete all expired tokens
            $this->auth->deleteExpiredTokens();
        } catch (\Exception $ex) {
            // We don't propagate this error
        }
        $contentBody = json_decode($request->getContent(), true);
        $username = $contentBody['security']['credentials']['login'] ?? '';
        $password = $contentBody['security']['credentials']['password'] ?? '';
        $contact = $this->auth->findContactByCredentials($username, $password);
        if (null !== $contact) {
            return [
                'contact'=> [
                    'id' => $contact->getId(),
                    'name' => $contact->getName(),
                    'alias' => $contact->getAlias(),
                    'email' => $contact->getEmail(),
                    'is_admin' => $contact->isAdmin()
                ],
                'security' => [
                    'token' => $this->auth->generateToken($contact->getAlias())
                ]
            ];
        }
        throw new HttpException(401, "Invalid credentials");
    }

    /**
     * Entry point used to delete an existing authentication token.
     *
     * @Rest\Get("/logout")
     * @Rest\View(populateDefaultVars=false)
     *
     * @param Request $request
     * @return string
     * @throws \RestException
     */
    public function logout(Request $request)
    {
        try {
            // We take this opportunity to delete all expired tokens
            $this->auth->deleteExpiredTokens();
        } catch (\Exception $ex) {
            // We don't propagate this error
        }
        try {
            $token = $request->headers->get('X-AUTH-TOKEN');
            $this->auth->logout($token);
            return 'Successful logout';
        } catch (\Exception $ex) {
            throw new \RestException($ex->getMessage());
        }
    }
}
