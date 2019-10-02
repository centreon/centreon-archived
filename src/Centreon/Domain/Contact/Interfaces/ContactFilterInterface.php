<?php

namespace Centreon\Domain\Contact\Interfaces;

interface ContactFilterInterface
{
    /**
     * Used to filter requests according to a contact.
     * If the filter is defined, all requests will use the ACL of the contact
     * to fetch data.
     *
     * @param mixed $contact Contact to use as a ACL filter
     * @throws \Exception
     */
    public function filterByContact($contact);
}
