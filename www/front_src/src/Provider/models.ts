import { KeyValuePair } from 'ramda';

import { User } from '@centreon/ui-context';

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

export interface DefaultParameters {
  monitoring_default_downtime_duration: string;
  monitoring_default_refresh_interval: string;
}
