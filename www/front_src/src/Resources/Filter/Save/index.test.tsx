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

const renderSaveMenu = (): RenderResult => render(<SaveMenuTest />);

const mockedAxios = axios as jest.Mocked<typeof axios>;

const filterId = 0;

const getFilter = ({ search = 'my search', name = 'MyFilter' }): Filter => ({
  id: filterId,
  name,
  criterias: [
    {
      name: 'resource_types',
      type: 'multi_select',
      object_type: null,
      value: [
        {
          id: 'host',
          name: 'Host',
        },
      ],
    },
    {
      name: 'states',
      type: 'multi_select',
      object_type: null,
      value: [
        {
          id: 'unhandled_problems',
          name: 'Unhandled',
        },
      ],
    },
    {
      name: 'statuses',
      type: 'multi_select',
      object_type: null,
      value: [
        {
          id: 'OK',
          name: 'Ok',
        },
      ],
    },
    {
      name: 'host_groups',
      type: 'multi_select',
      value: [
        {
          id: 0,
          name: 'Linux-servers',
        },
      ],
      object_type: 'host_groups',
    },
    {
      name: 'service_groups',
      type: 'multi_select',
      value: [
        {
          id: 0,
          name: 'Web-services',
        },
      ],
      object_type: 'service_groups',
    },
    {
      name: 'search',
      type: 'text',
      value: search,
      object_type: null,
    },
    {
      name: 'sort',
      type: 'array',
      value: [defaultSortField, defaultSortOrder],
      object_type: null,
    },
  ],
});

const retrievedCustomFilters = {
  result: [getFilter({})],
  meta: {
    page: 1,
    limit: 30,
    total: 1,
  },
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
      context.setFilter(filter);

      context.setNextSearch('toto');
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

    const updatedFilterRaw = getFilter({ search: newSearch });

    mockedAxios.put.mockResolvedValue({ data: updatedFilterRaw });

    act(() => {
      context.setFilter(filter);

      context.setNextSearch(newSearch);
    });

    expect(
      last(getAllByText(labelSave))?.parentElement?.parentElement,
    ).toHaveAttribute('aria-disabled', 'false');

    fireEvent.click(last(getAllByText(labelSave)) as HTMLElement);

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        `${filterEndpoint}/${context.updatedFilter.id}`,
        omit(['id'], getFilter({ search: newSearch })),
        expect.anything(),
      );
    });
  });
});
