import { KeyValuePair } from 'ramda';

export interface DefaultParameters {
  monitoring_default_acknowledgement_force_active_checks: boolean;
  monitoring_default_acknowledgement_notify: boolean;
  monitoring_default_acknowledgement_persistent: boolean;
  monitoring_default_acknowledgement_sticky: boolean;
  monitoring_default_acknowledgement_with_services: boolean;
  monitoring_default_downtime_duration: string;
  monitoring_default_downtime_fixed: boolean;
  monitoring_default_downtime_with_services: boolean;
  monitoring_default_refresh_interval: string;
}

type Translation = KeyValuePair<string, string>;
export type Translations = KeyValuePair<string, Translation>;
