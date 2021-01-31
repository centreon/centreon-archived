import { SelectEntry } from '@centreon/ui/src';

import { Criteria } from './models';

interface DefaultCriteriaValues {
  resourceTypes?: Array<SelectEntry>;
  states?: Array<SelectEntry>;
  statuses?: Array<SelectEntry>;
  hostGroups?: Array<SelectEntry>;
  serviceGroups?: Array<SelectEntry>;
}

const defaultSortField = 'status_severity_code';
const defaultSortOrder = 'asc';

const getDefaultCriterias = (
  {
    resourceTypes = [],
    states = [],
    statuses = [],
    hostGroups = [],
    serviceGroups = [],
  }: DefaultCriteriaValues = {
    resourceTypes: [],
    states: [],
    statuses: [],
    hostGroups: [],
    serviceGroups: [],
  },
): Array<Criteria> => {
  return [
    {
      name: 'resource_types',
      value: resourceTypes,
      type: 'multi_select',
    },
    {
      name: 'states',
      value: states,
      type: 'multi_select',
    },
    {
      name: 'statuses',
      value: statuses,
      type: 'multi_select',
    },
    {
      name: 'host_groups',
      value: hostGroups,
      type: 'multi_select',
      object_type: 'host_groups',
    },
    {
      name: 'service_groups',
      value: serviceGroups,
      type: 'multi_select',
      object_type: 'service_groups',
    },
    {
      name: 'search',
      value: '',
      type: 'text',
    },
    {
      name: 'sort',
      value: [defaultSortField, defaultSortOrder],
      type: 'array',
    },
  ];
};

export default getDefaultCriterias;
export { defaultSortField, defaultSortOrder };
