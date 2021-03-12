import { ListingModel } from '@centreon/ui';

export interface NamedEntity {
  uuid: string;
  id: number;
  name: string;
}

export interface Icon {
  url: string;
  name: string;
}

type ParentLinks = Pick<ResourceLinks, 'uris'>;

export interface Parent extends NamedEntity {
  icon: Icon | null;
  status: Status;
  links: ParentLinks;
  type?: string;
}

export interface Status {
  severity_code: number;
  name: string;
}

export interface Resource extends NamedEntity {
  icon?: Icon;
  parent?: Parent;
  status: Status;
  links: ResourceLinks;
  acknowledged: boolean;
  in_downtime: boolean;
  duration: string;
  tries: string;
  last_check: string;
  information: string;
  severity_level: number;
  short_type: 'h' | 's';
  type: 'host' | 'service';
  passive_checks: boolean;
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
  details: string | null;
  performance_graph: string | null;
  status_graph: string | null;
  timeline: string | null;
  acknowledgement: string | null;
  downtime: string | null;
}

export interface ResourceUris {
  configuration: string | null;
  logs: string | null;
  reporting: string | null;
}

interface Notes {
  label: string | null;
  url: string;
}

export interface ResourceExternals {
  action_url: string | null;
  notes: Notes | null;
}

export interface ResourceLinks {
  endpoints: ResourceEndpoints;
  uris: ResourceUris;
  externals: ResourceExternals;
}

export type TranslationType = (label: string) => string;

export type SortOrder = 'asc' | 'desc';
