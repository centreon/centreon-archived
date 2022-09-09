import { JsonDecoder } from 'ts.data.json';

import {
  ContactGroup,
  Contact,
  ContactsResult,
  ContactGroupsResult,
} from '../models';

const contactDecoder = JsonDecoder.object<Contact>(
  {
    alias: JsonDecoder.string,
    configuration_uri: JsonDecoder.string,
    email: JsonDecoder.string,
    id: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'contact',
);
const listContactsDecoder = JsonDecoder.array(contactDecoder, 'list contact');

export const contactsResultDecoder = JsonDecoder.object<ContactsResult>(
  {
    contacts: listContactsDecoder,
  },
  'contacts result',
);

const contactGroupsDecoder = JsonDecoder.object<ContactGroup>(
  {
    alias: JsonDecoder.string,
    configuration_uri: JsonDecoder.string,
    id: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'contactGroups',
);

const listContactGroupsDecoder = JsonDecoder.array(
  contactGroupsDecoder,
  'list contact groups',
);

export const contactGroupsResultDecoder =
  JsonDecoder.object<ContactGroupsResult>(
    {
      contact_groups: listContactGroupsDecoder,
    },
    'contact groups result',
  );
