import { GraphOptionId } from '../Graph/Performance/models';
import {
  Status,
  Acknowledgement,
  Downtime,
  Parent,
  ResourceLinks,
  NamedEntity,
  ResourceType,
} from '../models';

import { CustomTimePeriod, TimePeriodId } from './tabs/Graph/models';
import { Contact } from './tabs/Notifications/models';

export interface Group extends NamedEntity {
  configuration_uri: string | null;
}

export interface ResourceDetails extends NamedEntity {
  acknowledged: boolean;
  acknowledgement?: Acknowledgement;
  active_checks: boolean;
  alias?: string;
  calculation_type?: string;
  command_line?: string;
  downtimes: Array<Downtime>;
  duration: string;
  execution_time: number;
  flapping: boolean;
  fqdn?: string;
  groups?: Array<Group>;
  information: string;
  last_check: string;
  last_notification: string;
  last_status_change: string;
  last_time_with_no_issue: string;
  latency: number;
  links: ResourceLinks;
  monitoring_server_name?: string;
  next_check: string;
  notification_enabled: boolean;
  notification_number: number;
  notification_policy: string;
  parent: Parent;
  passive_checks?: boolean;
  percent_state_change: number;
  performance_data?: string;
  severity_level: number;
  status: Status;
  timezone?: string;
  tries: string;
  type: ResourceType;
  uuid: string;
}

export interface GraphOption {
  id: GraphOptionId;
  label: string;
  value: boolean;
}

export interface GraphOptions {
  [GraphOptionId.displayEvents]: GraphOption;
}

export interface GraphTabParameters {
  options?: GraphOptions;
}

export interface ServicesTabParameters {
  options: GraphOptions;
}

export interface TabParameters {
  graph?: GraphTabParameters;
  services?: ServicesTabParameters;
}

export interface DetailsUrlQueryParameters {
  customTimePeriod?: CustomTimePeriod;
  id: number;
  parentId?: number;
  parentType?: string;
  selectedTimePeriodId?: TimePeriodId;
  tab?: string;
  tabParameters?: TabParameters;
  type: string;
  uuid: string;
}
