import {
  Status,
  Acknowledgement,
  Downtime,
  Parent,
  ResourceLinks,
  Severity,
  NamedEntity,
} from '../models';

import { TimePeriodId } from './tabs/Graph/models';

export interface ResourceDetails {
  id: number;
  name: string;
  status: Status;
  parent: Parent;
  links: ResourceLinks;
  severity: Severity;
  information: string;
  downtimes: Array<Downtime>;
  acknowledgement?: Acknowledgement;
  acknowledged: boolean;
  duration: string;
  tries: string;
  poller_name?: string;
  timezone?: string;
  last_status_change: string;
  last_check: string;
  next_check: string;
  active_checks: boolean;
  execution_time: number;
  latency: number;
  flapping: boolean;
  percent_state_change: number;
  last_notification: string;
  notification_number: number;
  performance_data?: string;
  command_line?: string;
  type: 'service' | 'host';
  fqdn?: string;
  alias?: string;
  groups?: Array<NamedEntity>;
}

export interface ServicesTabParameters {
  graphMode: boolean;
  selectedTimePeriodId?: TimePeriodId;
}

export interface GraphTabParameters {
  selectedTimePeriodId?: TimePeriodId;
}
export interface TabParameters {
  services?: ServicesTabParameters;
  graph?: GraphTabParameters;
}

export interface DetailsUrlQueryParameters {
  id: number;
  parentId?: number;
  parentType?: string;
  type: string;
  tab?: string;
  tabParameters?: TabParameters;
}
