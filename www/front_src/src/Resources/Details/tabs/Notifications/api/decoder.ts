import { JsonDecoder } from 'ts.data.json';

import {
  ContactGroup,
  Contact,
  ContactsResult,
  ContactGroupResult,
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
  'contact result',
);

const contactGroupDecoder = JsonDecoder.object<ContactGroup>(
  {
    alias: JsonDecoder.string,
    configuration_uri: JsonDecoder.string,
    id: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'contactGroup',
);

const listContactsGroupDecoder = JsonDecoder.array(
  contactGroupDecoder,
  'list contact group',
);

export const contactsGroupResultDecoder =
  JsonDecoder.object<ContactGroupResult>(
    {
      contactsGroup: listContactsGroupDecoder,
    },
    'contact group result',
  );
s;
