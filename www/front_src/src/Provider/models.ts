import { KeyValuePair } from 'ramda';

export interface User {
  locale: string;
  timezone: string;
  alias: string;
  name: string;
}

export type UserContext = {
  acl: Acl;
  downtime: Downtime;
  refreshInterval: number;
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

export interface DefaultParameters {
  monitoring_default_downtime_duration: string;
  monitoring_default_refresh_interval: string;
}

export interface Downtime {
  default_duration: number;
}
