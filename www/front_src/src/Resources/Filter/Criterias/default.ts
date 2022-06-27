import { SelectEntry } from '@centreon/ui';

import { SortOrder } from '../../models';

import { Criteria } from './models';

interface DefaultCriteriaValues {
  hostCategories?: Array<SelectEntry>;
  hostGroups?: Array<SelectEntry>;
  hostSeverities?: Array<SelectEntry>;
  hostSeveritiesLevel?: Array<SelectEntry>;
  monitoringServers?: Array<SelectEntry>;
  resourceTypes?: Array<SelectEntry>;
  serviceCategories?: Array<SelectEntry>;
  serviceGroups?: Array<SelectEntry>;
  serviceSeverities?: Array<SelectEntry>;
  serviceSeveritiesLeveL?: Array<SelectEntry>;
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
    serviceSeverities = [],
    serviceSeveritiesLeveL = [],
    hostSeverities = [],
    hostSeveritiesLevel = [],
    statuses = [],
    hostGroups = [],
    serviceGroups = [],
    monitoringServers = [],
    statusTypes = [],
    hostCategories = [],
    serviceCategories = [],
  }: DefaultCriteriaValues = {
    hostGroups: [],
    hostSeverities: [],
    hostSeveritiesLevel: [],
    monitoringServers: [],
    resourceTypes: [],
    serviceCategories: [],
    serviceGroups: [],
    serviceSeverities: [],
    serviceSeveritiesLeveL: [],
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
      name: 'host_severities',
      object_type: 'host_severities',
      type: 'multi_select',
      value: hostSeverities,
    },
    {
      name: 'host_severities_level',
      object_type: 'host_severities_level',
      type: 'multi_select',
      value: hostSeveritiesLevel,
    },
    {
      name: 'service_groups',
      object_type: 'service_groups',
      type: 'multi_select',
      value: serviceGroups,
    },
    {
      name: 'service_severities',
      object_type: 'service_severities',
      type: 'multi_select',
      value: serviceSeverities,
    },
    {
      name: 'service_severities_level',
      object_type: 'service_severities_level',
      type: 'multi_select',
      value: serviceSeveritiesLeveL,
    },
    {
      name: 'monitoring_servers',
      object_type: 'monitoring_servers',
      type: 'multi_select',
      value: monitoringServers,
    },
    {
      name: 'host_categories',
      object_type: 'host_categories',
      type: 'multi_select',
      value: hostCategories,
    },
    {
      name: 'service_categories',
      object_type: 'service_categories',
      type: 'multi_select',
      value: serviceCategories,
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
