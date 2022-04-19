import axios from 'axios';
import { render, act, waitFor, RenderResult } from '@testing-library/react';
import { Provider } from 'jotai';

import { refreshIntervalAtom, userAtom } from '@centreon/ui-context';

import useFilter from '../../testUtils/useFilter';
import useListing from '../useListing';
import Context, { ResourceContext } from '../../testUtils/Context';
import useLoadDetails from '../../testUtils/useLoadDetails';

import useLoadResources from '.';

jest.mock('@centreon/ui-context', () =>
  jest.requireActual('centreon-frontend/packages/ui-context'),
);

const mockedAxios = axios as jest.Mocked<typeof axios>;

const mockUser = {
  locale: 'en',
  timezone: 'Europe/Paris',
};
const mockRefreshInterval = 60;

let context: ResourceContext;

const LoadResourcesComponent = (): JSX.Element => {
  useLoadResources();

  return <div />;
};

const TestComponent = (): JSX.Element => {
  const filterState = useFilter();
  const listingState = useListing();
  const detailsState = useLoadDetails();

  context = {
    ...filterState,
    ...listingState,
    ...detailsState,
  } as ResourceContext;

  return (
    <Context.Provider value={context}>
      <LoadResourcesComponent />
    </Context.Provider>
  );
};

const TestComponentWithJotai = (): JSX.Element => (
  <Provider
    initialValues={[
      [userAtom, mockUser],
      [refreshIntervalAtom, mockRefreshInterval],
    ]}
  >
    <TestComponent />
  </Provider>
);

const renderLoadResources = (): RenderResult =>
  render(<TestComponentWithJotai />);

describe(useLoadResources, () => {
  beforeEach(() => {
    mockedAxios.get.mockResolvedValue({
      data: {
        meta: {
          limit: 30,
          page: 1,
          total: 0,
        },
        result: [],
      },
    });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  const testCases = [
    [
      'sort',
      (): void => context.setCriteria?.({ name: 'sort', value: ['a', 'asc'] }),
      '2',
    ],
    ['limit', (): void => context.setLimit?.(20), '2'],
    [
      'search',
      (): void => context.setCriteria?.({ name: 'search', value: 'toto' }),
      '3',
    ],
    [
      'states',
      (): void =>
        context.setCriteria?.({
          name: 'states',
          value: [{ id: 'unhandled', name: 'Unhandled problems' }],
        }),
      '3',
    ],
    [
      'statuses',
      (): void =>
        context.setCriteria?.({
          name: 'statuses',
          value: [{ id: 'OK', name: 'Ok' }],
        }),
      '3',
    ],
    [
      'resourceTypes',
      (): void =>
        context.setCriteria?.({
          name: 'resource_types',
          value: [{ id: 'host', name: 'Host' }],
        }),
      '3',
    ],
    [
      'hostGroups',
      (): void =>
        context.setCriteria?.({
          name: 'host_groups',
          value: [{ id: 0, name: 'Linux-servers' }],
        }),
      '3',
    ],
    [
      'serviceGroups',
      (): void =>
        context.setCriteria?.({
          name: 'service_groups',
          value: [{ id: 1, name: 'Web-services' }],
        }),
      '3',
    ],
  ];

  it.each(testCases)(
    'resets the page to 1 when %p is changed and current filter is applied',
    async (_, setter, calls) => {
      renderLoadResources();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledTimes(
          parseInt(calls as string, 10),
        );
      });

      act(() => {
        context.setPage?.(2);
      });

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalled();
      });

      act(() => {
        (setter as () => void)();
        context.applyCurrentFilter?.();
      });

      await waitFor(() => {
        expect(context.page).toEqual(1);
        expect(mockedAxios.get).toHaveBeenCalled();
      });
    },
  );
});
