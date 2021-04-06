import { ListingModel } from '@centreon/ui';

export interface Icon {
  name: string;
  url: string;
}

type ParentLinks = Pick<ResourceLinks, 'uris'>;

export interface Parent {
  icon: Icon | null;
  id: number;
  links: ParentLinks;
  name: string;
  status: Status;
  type?: string;
}

export interface Status {
  name: string;
  severity_code: number;
}

export interface Resource {
  acknowledged: boolean;
  duration: string;
  icon?: Icon;
  id: number;
  in_downtime: boolean;
  information: string;
  last_check: string;
  links: ResourceLinks;
  name: string;
  parent?: Parent;
  passive_checks: boolean;
  severity_level: number;
  short_type: 'h' | 's';
  status: Status;
  tries: string;
  type: 'host' | 'service';
  uuid: string;
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
  acknowledgement: string | null;
  details: string | null;
  downtime: string | null;
  performance_graph: string | null;
  status_graph: string | null;
  timeline: string | null;
}

export interface ResourceUris {
  configuration: string | null;
  logs: string | null;
  reporting: string | null;
}

export interface ResourceExternals {
  notes_url: string | null;
}

export interface ResourceLinks {
  endpoints: ResourceEndpoints;
  externals: ResourceExternals;
  uris: ResourceUris;
}
