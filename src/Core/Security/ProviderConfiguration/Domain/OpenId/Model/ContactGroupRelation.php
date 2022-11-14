<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Security\ProviderConfiguration\Domain\OpenId\Model;

use Core\Contact\Domain\Model\ContactGroup;

/**
 * This class is designed to represent the relation between a Provider Claim and a contact Group.
 */
class ContactGroupRelation
{
    /**
     * @param string $claimValue
     * @param ContactGroup $contactGroup
     */
    public function __construct(private string $claimValue, private ContactGroup $contactGroup)
    {
    }

    /**
     * @return string
     */
    public function getClaimValue(): string
    {
        return $this->claimValue;
    }

    /**
     * @return ContactGroup
     */
    public function getContactGroup(): ContactGroup
    {
        return $this->contactGroup;
    }
}
