import {
  labelUnhandledProblems,
  labelResourceProblems,
  labelAll,
} from './translatedLabels';

export interface Icon {
  url: string;
  name: string;
}

export interface Parent {
  name: string;
  icon: Icon | null;
  status: Status;
}

export interface Status {
  code: number;
  name: string;
}

export interface Severity {
  level: number;
}

export interface Resource {
  id: string;
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
  short_name: string;
}

interface ListingMeta {
  page: number;
  limit: number;
  search: {};
  sort_by: {};
  total: number;
}

export interface Listing<TEntity> {
  result: Array<TEntity>;
  meta: ListingMeta;
}

export type ResourceListing = Listing<Resource>;

export type FilterId = 'unhandled_problems' | 'resources_problems' | 'all';

export interface Filter {
  id: FilterId;
  name: string;
}

export const unhandledProblemsFilter: Filter = {
  id: 'unhandled_problems',
  name: labelUnhandledProblems,
};

export const resourcesProblemFilter: Filter = {
  id: 'resources_problems',
  name: labelResourceProblems,
};

export const allFilter: Filter = {
  id: 'all',
  name: labelAll,
};
