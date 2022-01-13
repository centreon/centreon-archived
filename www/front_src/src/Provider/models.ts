import { KeyValuePair } from 'ramda';

export interface DefaultParameters {
  monitoring_default_acknowledgement_persistent: boolean;
  monitoring_default_acknowledgement_sticky: boolean;
  monitoring_default_downtime_duration: string;
  monitoring_default_refresh_interval: string;
}

type Translation = KeyValuePair<string, string>;
export type Translations = KeyValuePair<string, Translation>;
