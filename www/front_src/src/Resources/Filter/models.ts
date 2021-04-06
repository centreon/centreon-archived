export interface CriteriaValue {
  id: number | string;
  name: string;
}

export interface Criterias {
  hostGroups: Array<CriteriaValue>;
  resourceTypes: Array<CriteriaValue>;
  search?: string;
  serviceGroups: Array<CriteriaValue>;
  states: Array<CriteriaValue>;
  statuses: Array<CriteriaValue>;
}

export interface Filter {
  criterias: Criterias;
  id: string | number;
  name: string;
}

export interface RawCriteria {
  name: string;
  object_type?: string;
  type: string;
  value?: Array<CriteriaValue> | string | boolean;
}

export interface RawFilter {
  criterias: Array<RawCriteria>;
  id: number | string;
  name: string;
}
