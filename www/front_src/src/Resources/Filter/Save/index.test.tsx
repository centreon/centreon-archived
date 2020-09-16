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
import SaveMenu from '.';
import useFilter from '../useFilter';
import Context, { ResourceContext } from '../../Context';
import {
  labelSaveFilter,
  labelSave,
  labelSaveAsNew,
  labelName,
} from '../../translatedLabels';

import { filterEndpoint } from '../api';
import { RawFilter, Filter } from '../models';

let filterState;

const SaveMenuTest = (): JSX.Element => {
  filterState = useFilter();

  return (
    <Context.Provider
      value={
        {
          ...filterState,
        } as ResourceContext
      }
    >
      <SaveMenu />
    </Context.Provider>
  );
};

const renderSaveMenu = (): RenderResult => render(<SaveMenuTest />);

const mockedAxios = axios as jest.Mocked<typeof axios>;

const rawFilterId = 0;

const getRawFilter = ({
  search = 'my search',
  name = 'MyFilter',
}): RawFilter => ({
  id: rawFilterId,
  name,
  criterias: [
    {
      name: 'resource_types',
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
    },
  ],
});

const retrievedCustomFilters = {
  result: [getRawFilter({})],
  meta: {
    page: 1,
    limit: 30,
    total: 1,
  },
};

const getCustomFilter = (): Filter =>
  filterState.customFilters.find(propEq('id', rawFilterId));

describe(SaveMenu, () => {
  beforeEach(() => {
    mockedAxios.get.mockResolvedValue({ data: retrievedCustomFilters });
    mockedAxios.put.mockResolvedValue({ data: {} });
    mockedAxios.post.mockResolvedValue({ data: getRawFilter({}) });
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
    const { criterias } = filter;

    act(() => {
      filterState.setFilter(filter);
      filterState.setResourceTypes(criterias.resourceTypes);
      filterState.setHostGroups(criterias.hostGroups);
      filterState.setServiceGroups(criterias.serviceGroups);
      filterState.setStates(criterias.states);
      filterState.setStatuses(criterias.statuses);

      filterState.setNextSearch('toto');
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
        omit(['id'], getRawFilter({ name: 'My new filter', search: 'toto' })),
        expect.anything(),
      );
    });
  });

  it('sends an updateFilter request when the "Save" command is clicked', async () => {
    const { getAllByText } = renderSaveMenu();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    const filter = getCustomFilter();
    const { criterias } = filter;

    const newSearch = 'new search';

    const updatedFilterRaw = getRawFilter({ search: newSearch });

    mockedAxios.put.mockResolvedValue({ data: updatedFilterRaw });

    act(() => {
      filterState.setFilter(filter);
      filterState.setResourceTypes(criterias.resourceTypes);
      filterState.setHostGroups(criterias.hostGroups);
      filterState.setServiceGroups(criterias.serviceGroups);
      filterState.setStates(criterias.states);
      filterState.setStatuses(criterias.statuses);

      filterState.setNextSearch(newSearch);
    });

    expect(
      last(getAllByText(labelSave))?.parentElement?.parentElement,
    ).toHaveAttribute('aria-disabled', 'false');

    fireEvent.click(last(getAllByText(labelSave)) as HTMLElement);

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        `${filterEndpoint}/${filterState.updatedFilter.id}`,
        omit(['id'], getRawFilter({ search: newSearch })),
        expect.anything(),
      );
    });
  });
});
