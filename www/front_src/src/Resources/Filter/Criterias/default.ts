import { SelectEntry } from '@centreon/ui';

import { SortOrder } from '../../models';

import { Criteria } from './models';

interface DefaultCriteriaValues {
  hostCategories?: Array<SelectEntry>;
  hostGroups?: Array<SelectEntry>;
  monitoringServers?: Array<SelectEntry>;
  resourceTypes?: Array<SelectEntry>;
  serviceCategories?: Array<SelectEntry>;
  serviceGroups?: Array<SelectEntry>;
  states?: Array<SelectEntry>;
  statusTypes?: Array<SelectEntry>;
  statuses?: Array<SelectEntry>;
}

const defaultSortField = 'status_severity_code';
const defaultSortOrder = SortOrder.asc;

const getDefaultCriterias = (
  {
    resourceTypes = [],
    states = [],
    statuses = [],
    hostGroups = [],
    serviceGroups = [],
    monitoringServers = [],
    statusTypes = [],
    serviceCategories = [],
    hostCategories = [],
  }: DefaultCriteriaValues = {
    hostCategories: [],
    hostGroups: [],
    monitoringServers: [],
    resourceTypes: [],
    serviceCategories: [],
    serviceGroups: [],
    states: [],
    statusTypes: [],
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
      name: 'status_types',
      object_type: null,
      type: 'multi_select',
      value: statusTypes,
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
      name: 'service_categories',
      object_type: 'service_categories',
      type: 'multi_select',
      value: serviceCategories,
    },
    {
      name: 'host_categories',
      object_type: 'host_categories',
      type: 'multi_select',
      value: hostCategories,
    },
    {
      name: 'monitoring_servers',
      object_type: 'monitoring_servers',
      type: 'multi_select',
      value: monitoringServers,
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
