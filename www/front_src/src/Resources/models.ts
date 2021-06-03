import { ListingModel } from '@centreon/ui';

export enum ResourceType {
  businessActivity = 'business-activity',
  host = 'host',
  metaservice = 'metaservice',
  service = 'service',
}

export type ResourceShortType = 'h' | 's' | 'm' | 'a';

export interface NamedEntity {
  id: number;
  name: string;
  uuid: string;
}

export interface Icon {
  name: string;
  url: string;
}

export type Parent = Omit<Resource, 'parent'>;
export interface Status {
  name: string;
  severity_code: number;
}

export interface Resource extends NamedEntity {
  acknowledged?: boolean;
  active_checks?: boolean;
  additionals?: ResourceAdditionals;
  duration?: string;
  icon?: Icon;
  in_downtime?: boolean;
  information?: string;
  last_check?: string;
  links?: ResourceLinks;
  notification_enabled?: boolean;
  parent?: Parent;
  passive_checks?: boolean;
  severity_level?: number;
  short_type: ResourceShortType;
  status?: Status;
  tries?: string;
  type: ResourceType;
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
  acknowledgement?: string;
  details?: string;
  downtime?: string;
  metrics?: string;
  performance_graph?: string;
  status_graph?: string;
  timeline?: string;
}

export interface ResourceUris {
  configuration?: string;
  logs?: string;
  reporting?: string;
}

export interface Notes {
  label?: string;
  url: string;
}

export interface ResourceExternals {
  action_url?: string;
  notes?: Notes;
}

export interface ResourceLinks {
  endpoints: ResourceEndpoints;
  externals?: ResourceExternals;
  uris: ResourceUris;
}

export enum ResourceCalculationMethod {
  bestStatus = 'best status',
  impact = 'impact',
  ratio = 'ratio',
  worstStatus = 'worst status',
}

export interface ResourceAdditionals {
  calculation_method: ResourceCalculationMethod;
  calculation_ratio_mode?: string;
  health?: number;
}

export type TranslationType = (label: string) => string;

export type SortOrder = 'asc' | 'desc';
