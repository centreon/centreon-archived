import { SelectEntry } from '@centreon/ui';

import { SortOrder } from '../../models';

import { Criteria } from './models';

interface DefaultCriteriaValues {
  statuses?: Array<SelectEntry>;
  types?: Array<SelectEntry>;
}

const defaultSortField = 'status_severity_code'; // ????
const defaultSortOrder = SortOrder.asc;

const getDefaultCriterias = (
  { statuses = [], types = [] }: DefaultCriteriaValues = {
    statuses: [],
    types: [],
  },
): Array<Criteria> => {
  return [
    {
      name: 'statuses',
      value: statuses,
    },
    {
      name: 'types',
      value: types,
    },

    {
      name: 'search',
      value: '',
    },
    {
      name: 'sort',
      value: [defaultSortField, defaultSortOrder],
    },
  ];
};

export default getDefaultCriterias;
