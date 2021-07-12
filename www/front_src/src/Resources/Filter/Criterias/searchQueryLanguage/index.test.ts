import { CriteriaValue } from '../models';

import { build, parse } from './index';

const search =
  'monitoring_server:1|Central host_group:53|Linux-Servers status:OK,UP state:unhandled_problems resource_type:host,service h.name:centreon';

const parsedSearch = [
  {
    name: 'service_groups',
    object_type: 'service_groups',
    type: 'multi_select',
    value: [],
  },
  {
    name: 'sort',
    object_type: null,
    type: 'array',
    value: ['status_severity_code', 'asc'] as CriteriaValue,
  },
  {
    name: 'monitoring_servers',
    object_type: 'monitoring_servers',
    type: 'multi_select',
    value: [{ id: 1, name: 'Central' }],
  },
  {
    name: 'host_groups',
    object_type: 'host_groups',
    type: 'multi_select',
    value: [{ id: 53, name: 'Linux-Servers' }],
  },
  {
    name: 'statuses',
    object_type: null,
    type: 'multi_select',
    value: [
      { id: 'OK', name: 'Ok' },
      { id: 'UP', name: 'Up' },
    ],
  },
  {
    name: 'states',
    object_type: null,
    type: 'multi_select',
    value: [{ id: 'unhandled_problems', name: 'Unhandled' }],
  },
  {
    name: 'resource_types',
    object_type: null,
    type: 'multi_select',
    value: [
      { id: 'host', name: 'Host' },
      { id: 'service', name: 'Service' },
    ],
  },
  { name: 'search', object_type: null, type: 'text', value: 'h.name:centreon' },
];

describe(parse, () => {
  it('parses the given search string into a Search model', () => {
    const result = parse(search);

    expect(result).toEqual(parsedSearch);
  });
});

describe(build, () => {
  it('builds a search string from the given Search model', () => {
    const result = build(parsedSearch);

    expect(result).toEqual(search);
  });
});
