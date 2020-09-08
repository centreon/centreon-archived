import { Status, Acknowledgement, Downtime, Parent, Severity } from '../models';

export interface ResourceDetails {
  display_name: string;
  status: Status;
  parent: Parent;
  severity: Severity;
  information: string;
  downtimes: Array<Downtime>;
  acknowledgement?: Acknowledgement;
  is_acknowledged: boolean;
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
}
