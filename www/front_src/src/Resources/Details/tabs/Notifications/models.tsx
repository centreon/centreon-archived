import { NamedEntity } from '../../../models';

export type ContactEntity = Omit<NamedEntity, 'uuid'>;

export interface Contact {
  alias: string;
  configuration_uri: string;
  email: string;
  id: number;
  name: string;
}

export interface ContactGroup {
  alias: string;
  configuration_uri: string;
  id: number;
  name: string;
}

export interface ContactsResult {
  contacts: Array<Contact>;
}

export interface ContactGroupResult {
  contactsGroup: Array<ContactGroup>;
}
