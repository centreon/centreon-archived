import { KeyValuePair } from 'ramda';

export interface User {
  locale: string;
  timezone: string;
  username: string;
}

export type UserContext = {
  acl: Acl;
} & User;

export interface ActionAcl {
  check: boolean;
  acknowledgement: boolean;
  disacknowledgement: boolean;
  downtime: boolean;
}

export interface Actions {
  service: ActionAcl;
  host: ActionAcl;
}

interface Acl {
  actions: Actions;
}

type Translation = KeyValuePair<string, string>;
export type Translations = KeyValuePair<string, Translation>;
