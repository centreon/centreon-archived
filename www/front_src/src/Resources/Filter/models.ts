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
}

export interface RawCriteria {
  name: string;
  object_type?: string;
  type: string;
  value?: Array<CriteriaValue> | string | boolean;
}

export interface RawFilter {
  id: number;
  name: string;
  criterias: Array<RawCriteria>;
}
