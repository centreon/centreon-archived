import { KeyValuePair } from 'ramda';

export interface User {
  locale: string;
  timezone: string;
  name: string;
  alias: string;
}

export type UserContext = {
  acl: Acl;
} & User;

export interface ActionAcl {
  check: boolean;
  acknowledgement: boolean;
  disacknowledgement: boolean;
  downtime: boolean;
  submit_status: boolean;
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

interface DowntimeParameters {
  default_duration: number;
}
export interface Parameters {
  user: User;
  downtime: DowntimeParameters;
  refresh_interval: number;
}
