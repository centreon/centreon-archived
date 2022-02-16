import { SelectEntry } from '@centreon/ui';

import { Criteria } from './models';

interface DefaultCriteriaValues {
  statuses?: Array<SelectEntry>;
  types?: Array<SelectEntry>;
}

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
  ];
};

export default getDefaultCriterias;
