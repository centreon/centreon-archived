import {
  Status,
  Acknowledgement,
  Downtime,
  Parent,
  ResourceLinks,
} from '../models';

export interface ResourceDetails {
  acknowledged: boolean;
  acknowledgement?: Acknowledgement;
  active_checks: boolean;
  alias?: string;
  command_line?: string;
  downtimes: Array<Downtime>;
  duration: string;
  execution_time: number;
  flapping: boolean;
  fqdn?: string;
  id: number;
  information: string;
  last_check: string;
  last_notification: string;
  last_status_change: string;
  latency: number;
  links: ResourceLinks;
  name: string;
  next_check: string;
  notification_number: number;
  parent: Parent;
  percent_state_change: number;
  performance_data?: string;
  poller_name?: string;
  severity_level: number;
  status: Status;
  timezone?: string;
  tries: string;
  type: 'service' | 'host';
}

export interface DetailsUrlQueryParameters {
  id: number;
  parentId?: number;
  parentType?: string;
  tab?: string;
  type: string;
  uuid: string;
}
