import { concat, pipe, prop, toLower } from 'ramda';

import { selectableResourceTypes, selectableStatuses } from '../models';

import { build, parse, getAutocompleteSuggestions } from './index';

const search =
  'type:host,service state:unhandled status:ok,up host_group:Linux-Servers monitoring_server:Central h.name:centreon';

const parsedSearch = [
  {
    name: 'resource_types',
    object_type: null,
    type: 'multi_select',
    value: [
      { id: 'host', name: 'Host' },
      { id: 'service', name: 'Service' },
    ],
  },
  {
    name: 'states',
    object_type: null,
    type: 'multi_select',
    value: [{ id: 'unhandled_problems', name: 'Unhandled' }],
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
    name: 'host_groups',
    object_type: 'host_groups',
    type: 'multi_select',
    value: [{ id: 0, name: 'Linux-Servers' }],
  },
  {
    name: 'service_groups',
    object_type: 'service_groups',
    type: 'multi_select',
    value: [],
  },
  {
    name: 'monitoring_servers',
    object_type: 'monitoring_servers',
    type: 'multi_select',
    value: [{ id: 0, name: 'Central' }],
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

describe(getAutocompleteSuggestions, () => {
  const testCases = [
    {
      cursorPosition: 3,
      expectedResult: ['state:', 'status:'],
      inputSearch: 'sta',
    },
    {
      cursorPosition: 6,
      expectedResult: ['unhandled', 'acknowledged', 'in_downtime'],
      inputSearch: 'state:',
    },
    {
      cursorPosition: 5,
      expectedResult: selectableResourceTypes.map(prop('id')),
      inputSearch: 'type:',
    },
    {
      cursorPosition: 15,
      expectedResult: [',acknowledged', ',in_downtime'],
      inputSearch: 'state:unhandled',
    },
    {
      cursorPosition: 16,
      expectedResult: ['acknowledged', 'in_downtime'],
      inputSearch: 'state:unhandled,',
    },
    {
      cursorPosition: 18,
      expectedResult: ['status:'],
      inputSearch: 'state:unhandled st',
    },
    {
      cursorPosition: 23,
      expectedResult: selectableStatuses.map(
        pipe(prop('id'), toLower),
        concat(','),
      ),
      inputSearch: 'state:unhandled status:',
    },
    {
      cursorPosition: 14,
      expectedResult: [],
      inputSearch: 'service_group:',
    },
    {
      cursorPosition: 11,
      expectedResult: [],
      inputSearch: 'host_group:',
    },
    {
      cursorPosition: 19,
      expectedResult: [],
      inputSearch: 'monitoring_server:',
    },
  ];

  it.each(testCases)(
    'returns "$expectedResult" when "$inputSearch" is input at the $cursorPosition cursor position',
    ({ inputSearch, cursorPosition, expectedResult }) => {
      expect(
        getAutocompleteSuggestions({
          cursorPosition,
          search: inputSearch,
        }),
      ).toEqual(expectedResult);
    },
  );
});
