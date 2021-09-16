<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Centreon\Application\Controller;

use Centreon\Domain\Contact\Contact;
use FOS\RestBundle\View\View;

/**
 * Used to manage allowed actions of the current user
 *
 * @package Centreon\Application\Controller
 */
class UserController extends AbstractController
{
    /**
     * Entry point to get acl actions of the current user.
     *
     * @return View
     */
    public function getActionsAuthorization(): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $actions = [
            'host' => [
                'check' => $this->getAuthorizationForRole(Contact::ROLE_HOST_CHECK),
                'acknowledgement' => $this->getAuthorizationForRole(Contact::ROLE_HOST_ACKNOWLEDGEMENT),
                'disacknowledgement' => $this->getAuthorizationForRole(Contact::ROLE_HOST_DISACKNOWLEDGEMENT),
                'downtime' => $this->getAuthorizationForRole(Contact::ROLE_ADD_HOST_DOWNTIME),
                'submit_status' => $this->getAuthorizationForRole(Contact::ROLE_HOST_SUBMIT_RESULT),
                'comment' => $this->getAuthorizationForRole(Contact::ROLE_HOST_ADD_COMMENT),
            ],
            'service' => [
                'check' => $this->getAuthorizationForRole(Contact::ROLE_SERVICE_CHECK),
                'acknowledgement' => $this->getAuthorizationForRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT),
                'disacknowledgement' => $this->getAuthorizationForRole(Contact::ROLE_SERVICE_DISACKNOWLEDGEMENT),
                'downtime' => $this->getAuthorizationForRole(Contact::ROLE_ADD_SERVICE_DOWNTIME),
                'submit_status' => $this->getAuthorizationForRole(Contact::ROLE_SERVICE_SUBMIT_RESULT),
                'comment' => $this->getAuthorizationForRole(Contact::ROLE_SERVICE_ADD_COMMENT),
            ],
            'metaservice' => [
                'check' => $this->getAuthorizationForRole(Contact::ROLE_SERVICE_CHECK),
                'acknowledgement' => $this->getAuthorizationForRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT),
                'disacknowledgement' => $this->getAuthorizationForRole(Contact::ROLE_SERVICE_DISACKNOWLEDGEMENT),
                'downtime' => $this->getAuthorizationForRole(Contact::ROLE_ADD_SERVICE_DOWNTIME),
                'submit_status' => $this->getAuthorizationForRole(Contact::ROLE_SERVICE_SUBMIT_RESULT),
                'comment' => $this->getAuthorizationForRole(Contact::ROLE_SERVICE_ADD_COMMENT),
            ],
        ];

        return $this->view($actions);
    }
    /**
     * Entry point to get configured parameters for the current user
     *
     * @return View
     */
    public function getUserParameters(): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $user = $this->getUser();

        return $this->view([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'alias' => $user->getAlias(),
            'email' => $user->getEmail(),
            'timezone' => $user->getTimezone()->getName(),
            'locale' => $user->getLocale(),
            'is_admin' => $user->isAdmin()
        ]);
    }

    /**
     * Get authorization for a specific role of the current user
     *
     * @param string $role
     * @return boolean
     */
    private function getAuthorizationForRole(string $role): bool
    {
        /**
         * @var Contact|null $contact
         */
        $contact = $this->getUser();

        if ($contact === null) {
            return false;
        }

        return $contact->isAdmin() || $contact->hasRole($role);
    }
}
