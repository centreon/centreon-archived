import * as React from 'react';

import axios from 'axios';
import { useSelector } from 'react-redux';

import { render, act, waitFor } from '@testing-library/react';

import useLoadResources from '.';
import useFilter from '../../Filter/useFilter';
import useListing from '../useListing';
import Context, { ResourceContext } from '../../Context';
import useDetails from '../../Details/useDetails';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('react-redux', () => ({
  useSelector: jest.fn(),
}));

let context: ResourceContext;

const LoadResourcesComponent = (): JSX.Element => {
  useLoadResources();

  return <></>;
};

const TestComponent = (): JSX.Element => {
  const filterState = useFilter();
  const listingState = useListing();
  const detailsState = useDetails();

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

const appState = {
  intervals: {
    AjaxTimeReloadMonitoring: 60,
  },
};

describe(useLoadResources, () => {
  beforeEach(() => {
    useSelector.mockImplementation((callback) => {
      return callback(appState);
    });

    mockedAxios.get.mockResolvedValue({
      data: {
        result: [],
        meta: {
          page: 1,
          limit: 30,
          total: 0,
        },
      },
    });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  const testCases = [
    ['sortf', (): void => context.setSortf('a')],
    ['sorto', (): void => context.setSorto('desc')],
    ['limit', (): void => context.setLimit(20), '20'],
    ['currentSearch', (): void => context.setCurrentSearch('toto')],
    [
      'states',
      (): void =>
        context.setStates([{ id: 'unhandled', name: 'Unhandled problems' }]),
    ],
    ['statuses', (): void => context.setStatuses([{ id: 'OK', name: 'Ok' }])],
    [
      'resourceTypes',
      (): void => context.setResourceTypes([{ id: 'host', name: 'Host' }]),
    ],
    [
      'hostGroups',
      (): void => context.setHostGroups([{ id: 0, name: 'Linux-servers' }]),
    ],
    [
      'serviceGroups',
      (): void => context.setServiceGroups([{ id: 1, name: 'Web-services' }]),
    ],
  ];

  it.each(testCases)(
    'resets the page to 1 when %p is changed',
    async (_, setter) => {
      render(<TestComponent />);

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledTimes(2);
      });

      act(() => {
        context.setPage(2);
      });

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalled();
      });

      act(() => {
        (setter as () => void)();
      });

      await waitFor(() => {
        expect(context.page).toEqual(1);
        expect(mockedAxios.get).toHaveBeenCalled();
      });
    },
  );
});
