import { SortOrder } from '../Listing/models';

export interface CriteriaValue {
  id: number | string;
  name: string;
}

export interface Criterias {
  resourceTypes: Array<CriteriaValue>;
  states: Array<CriteriaValue>;
  statuses: Array<CriteriaValue>;
  hostGroups: Array<CriteriaValue>;
  serviceGroups: Array<CriteriaValue>;
  search?: string;
}

export interface Filter {
  id: string | number;
  name: string;
  criterias: Criterias;
  sort: [string, SortOrder];
}

export interface RawCriteria {
  name: string;
  object_type?: string;
  type: string;
  value?: Array<CriteriaValue> | string | boolean | [string, SortOrder];
}

export interface RawFilter {
  id: number | string;
  name: string;
  criterias: Array<RawCriteria>;
}
