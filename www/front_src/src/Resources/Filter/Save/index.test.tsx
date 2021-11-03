import * as React from 'react';

import {
  render,
  RenderResult,
  fireEvent,
  waitFor,
  act,
} from '@testing-library/react';
import axios from 'axios';
import { last, omit, propEq } from 'ramda';
import userEvent from '@testing-library/user-event';
import { Provider } from 'jotai';

import useFilter from '../useFilter';
import Context, { ResourceContext } from '../../Context';
import {
  labelSaveFilter,
  labelSave,
  labelSaveAsNew,
  labelName,
} from '../../translatedLabels';
import { filterEndpoint } from '../api';
import { Filter } from '../models';
import useListing from '../../Listing/useListing';
import { defaultSortField, defaultSortOrder } from '../Criterias/default';
import { getFilterWithUpdatedCriteria } from '../../testUtils';

import SaveMenu from '.';

let context;

const SaveMenuTest = (): JSX.Element => {
  const listingState = useListing();
  const filterState = useFilter();

  context = {
    ...listingState,
    ...filterState,
  };

  return (
    <Context.Provider
      value={
        {
          ...context,
        } as ResourceContext
      }
    >
      <SaveMenu />
    </Context.Provider>
  );
};

const SaveMenuTestWithJotai = (): JSX.Element => (
  <Provider>
    <SaveMenuTest />
  </Provider>
);

const renderSaveMenu = (): RenderResult => render(<SaveMenuTestWithJotai />);

const mockedAxios = axios as jest.Mocked<typeof axios>;

const filterId = 0;

const getFilter = ({ search = 'my search', name = 'MyFilter' }): Filter => ({
  criterias: [
    {
      name: 'resource_types',
      object_type: null,
      type: 'multi_select',
      value: [
        {
          id: 'host',
          name: 'Host',
        },
      ],
    },
    {
      name: 'states',
      object_type: null,
      type: 'multi_select',
      value: [
        {
          id: 'unhandled_problems',
          name: 'Unhandled',
        },
      ],
    },
    {
      name: 'statuses',
      object_type: null,
      type: 'multi_select',
      value: [
        {
          id: 'OK',
          name: 'Ok',
        },
      ],
    },
    {
      name: 'host_groups',
      object_type: 'host_groups',
      type: 'multi_select',
      value: [
        {
          id: 0,
          name: 'Linux-servers',
        },
      ],
    },
    {
      name: 'service_groups',
      object_type: 'service_groups',
      type: 'multi_select',
      value: [
        {
          id: 0,
          name: 'Web-services',
        },
      ],
    },
    {
      name: 'monitoring_servers',
      object_type: 'monitoring_servers',
      type: 'multi_select',
      value: [],
    },
    {
      name: 'search',
      object_type: null,
      type: 'text',
      value: search,
    },
    {
      name: 'sort',
      object_type: null,
      type: 'array',
      value: [defaultSortField, defaultSortOrder],
    },
  ],
  id: filterId,
  name,
});

const retrievedCustomFilters = {
  meta: {
    limit: 30,
    page: 1,
    total: 1,
  },
  result: [getFilter({})],
};

const getCustomFilter = (): Filter =>
  context.customFilters.find(propEq('id', filterId));

describe(SaveMenu, () => {
  beforeEach(() => {
    mockedAxios.get.mockResolvedValue({ data: retrievedCustomFilters });
    mockedAxios.put.mockResolvedValue({ data: {} });
    mockedAxios.post.mockResolvedValue({ data: getFilter({}) });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.put.mockReset();
    mockedAxios.post.mockReset();
  });

  it('disables save menus when the current filter has no changes', async () => {
    const { getByTitle, getAllByText } = renderSaveMenu();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    userEvent.click(getByTitle(labelSaveFilter));

    expect(last(getAllByText(labelSaveAsNew))).toHaveAttribute(
      'aria-disabled',
      'true',
    );

    expect(
      last(getAllByText(labelSave))?.parentElement?.parentElement,
    ).toHaveAttribute('aria-disabled', 'true');
  });

  it('sends a createFilter request when the "Save as new" command is clicked', async () => {
    const { getAllByText, getByLabelText } = renderSaveMenu();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    const filter = getCustomFilter();

    act(() => {
      context.setCurrentFilter(
        getFilterWithUpdatedCriteria({
          criteriaName: 'search',
          criteriaValue: 'toto',
          filter,
        }),
      );
    });

    expect(
      last(getAllByText(labelSave))?.parentElement?.parentElement,
    ).toHaveAttribute('aria-disabled', 'false');

    fireEvent.click(last(getAllByText(labelSaveAsNew)) as HTMLElement);

    act(() => {
      fireEvent.change(getByLabelText(labelName), {
        target: {
          value: 'My new filter',
        },
      });
    });

    fireEvent.click(last(getAllByText(labelSave)) as HTMLElement);

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalledWith(
        filterEndpoint,
        omit(['id'], getFilter({ name: 'My new filter', search: 'toto' })),
        expect.anything(),
      );
    });
  });

  it('sends an updateFilter request when the "Save" command is clicked', async () => {
    const { getAllByText } = renderSaveMenu();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    const filter = getCustomFilter();

    const newSearch = 'new search';

    const updatedFilter = getFilter({ search: newSearch });

    mockedAxios.put.mockResolvedValue({ data: updatedFilter });

    act(() => {
      context.setCurrentFilter(
        getFilterWithUpdatedCriteria({
          criteriaName: 'search',
          criteriaValue: newSearch,
          filter,
        }),
      );
    });

    expect(
      last(getAllByText(labelSave))?.parentElement?.parentElement,
    ).toHaveAttribute('aria-disabled', 'false');

    fireEvent.click(last(getAllByText(labelSave)) as HTMLElement);

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        `${filterEndpoint}/${context.currentFilter.id}`,
        omit(['id'], getFilter({ search: newSearch })),
        expect.anything(),
      );
    });
  });
});
