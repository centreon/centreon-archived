import { SelectEntry } from '@centreon/ui';

import { Criteria } from './models';

interface DefaultCriteriaValues {
  hostGroups?: Array<SelectEntry>;
  resourceTypes?: Array<SelectEntry>;
  serviceGroups?: Array<SelectEntry>;
  states?: Array<SelectEntry>;
  statuses?: Array<SelectEntry>;
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
    hostGroups: [],
    resourceTypes: [],
    serviceGroups: [],
    states: [],
    statuses: [],
  },
): Array<Criteria> => {
  return [
    {
      name: 'resource_types',
      object_type: null,
      type: 'multi_select',
      value: resourceTypes,
    },
    {
      name: 'states',
      object_type: null,
      type: 'multi_select',
      value: states,
    },
    {
      name: 'statuses',
      object_type: null,
      type: 'multi_select',
      value: statuses,
    },
    {
      name: 'host_groups',
      object_type: 'host_groups',
      type: 'multi_select',
      value: hostGroups,
    },
    {
      name: 'service_groups',
      object_type: 'service_groups',
      type: 'multi_select',
      value: serviceGroups,
    },
    {
      name: 'search',
      object_type: null,
      type: 'text',
      value: '',
    },
    {
      name: 'sort',
      object_type: null,
      type: 'array',
      value: [defaultSortField, defaultSortOrder],
    },
  ];
};

const getAllCriterias = (): Array<Criteria> => [
  ...getDefaultCriterias(),
  {
    name: 'monitoring_servers',
    object_type: 'monitoring_servers',
    type: 'multi_select',
    value: [],
  },
];

export default getDefaultCriterias;
export { defaultSortField, defaultSortOrder, getAllCriterias };
