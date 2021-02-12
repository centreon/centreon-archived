import { isNil } from 'ramda';

import {
  labelAll,
  labelNewFilter,
  labelUnhandledProblems,
  labelResourceProblems,
} from '../translatedLabels';

import getDefaultCriterias from './Criterias/default';
import {
  Criteria,
  criticalStatus,
  downStatus,
  selectableResourceTypes,
  selectableStates,
  selectableStatuses,
  unhandledState,
  unknownStatus,
  warningStatus,
} from './Criterias/models';

export interface Filter {
  id: number | string;
  name: string;
  criterias: Array<Criteria>;
}

const allFilter = {
  id: 'all',
  name: labelAll,
  criterias: getDefaultCriterias(),
};

const newFilter = {
  id: '',
  name: labelNewFilter,
} as Filter;

const unhandledProblemsFilter: Filter = {
  id: 'unhandled_problems',
  name: labelUnhandledProblems,
  criterias: getDefaultCriterias({
    states: [unhandledState],
    statuses: [warningStatus, downStatus, criticalStatus, unknownStatus],
  }),
};

const resourceProblemsFilter: Filter = {
  id: 'resource_problems',
  name: labelResourceProblems,
  criterias: getDefaultCriterias({
    statuses: [warningStatus, downStatus, criticalStatus, unknownStatus],
  }),
};

const standardFilterById = {
  resource_problems: resourceProblemsFilter,
  all: allFilter,
  unhandled_problems: unhandledProblemsFilter,
};

const isCustom = ({ id }: Filter): boolean => {
  return isNil(standardFilterById[id]);
};

export {
  allFilter,
  unhandledProblemsFilter,
  resourceProblemsFilter,
  newFilter,
  selectableResourceTypes,
  selectableStates,
  selectableStatuses,
  standardFilterById,
  isCustom,
};
