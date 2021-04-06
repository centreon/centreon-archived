import { Status, Acknowledgement, Downtime, Parent } from '../models';

export interface ResourceDetails {
  acknowledgement?: Acknowledgement;
  active_checks: boolean;
  command_line?: string;
  criticality: number;
  display_name: string;
  downtimes: Array<Downtime>;
  duration: string;
  execution_time: number;
  flapping: boolean;
  is_acknowledged: boolean;
  last_check: string;
  last_notification: string;
  last_state_change: string;
  latency: number;
  next_check: string;
  notification_number: number;
  output: string;
  parent: Parent;
  percent_state_change: number;
  performance_data?: string;
  poller_name?: string;
  status: Status;
  timezone?: string;
  tries: string;
}
