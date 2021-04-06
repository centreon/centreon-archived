import { ListingModel } from '@centreon/ui';

export interface Icon {
  name: string;
  url: string;
}

export interface Parent {
  configuration_uri?: string;
  icon: Icon | null;
  id: number;
  logs_uri?: string;
  name: string;
  reporting_uri?: string;
  status: Status;
}

export interface Status {
  code: number;
  name: string;
  severity_code: number;
}

export interface Severity {
  level: number;
}

export interface Resource {
  acknowledged: boolean;
  acknowledgement_endpoint?: string;
  configuration_uri?: string;
  details_endpoint: string;
  downtime_endpoint?: string;
  duration: string;
  icon?: Icon;
  id: number;
  in_downtime: boolean;
  information: string;
  last_check: string;
  logs_uri?: string;
  name: string;
  parent?: Parent;
  performance_graph_endpoint?: string;
  reporting_uri?: string;
  severity?: Severity;
  short_type: 'h' | 's';
  status: Status;
  timeline_endpoint: string;
  tries: string;
  type: 'host' | 'service';
}

export type ResourceListing = ListingModel<Resource>;

export interface Downtime {
  author_name: string;
  comment: string;
  end_time: string;
  entry_time: string;
  start_time: string;
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
  performanceGraph?: string;
  timeline: string;
}

export interface ResourceUris {
  configuration?: string;
  logs?: string;
  reporting?: string;
}

export interface ResourceLinks {
  endpoints: ResourceEndpoints;
  uris: { parent: ResourceUris; resource: ResourceUris };
}
