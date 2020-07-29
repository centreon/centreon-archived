import { ListingModel } from '@centreon/ui';

export interface Icon {
  url: string;
  name: string;
}

export interface Parent {
  id: number;
  name: string;
  icon: Icon | null;
  status: Status;
}

export interface Status {
  severity_code: number;
  code: number;
  name: string;
}

export interface Severity {
  level: number;
}

export interface Resource {
  id: number;
  name: string;
  icon?: Icon;
  parent?: Parent;
  status: Status;
  downtime_endpoint?: string;
  acknowledged: boolean;
  acknowledgement_endpoint?: string;
  in_downtime: boolean;
  duration: string;
  tries: string;
  last_check: string;
  information: string;
  severity?: Severity;
  short_type: 'h' | 's';
  performance_graph_endpoint?: string;
  type: 'host' | 'service';
  details_endpoint: string;
  timeline_endpoint: string;
}

export type ResourceListing = ListingModel<Resource>;

export interface Downtime {
  author_name: string;
  comment: string;
  entry_time: string;
  start_time: string;
  end_time: string;
}

export interface Acknowledgement {
  author_name: string;
  comment: string;
  entry_time: string;
  is_persistent: boolean;
  is_sticky: boolean;
}

export interface ResourceEndpoints {
  details: string;
  statusGraph?: string;
  performanceGraph?: string;
  timeline: string;
}
