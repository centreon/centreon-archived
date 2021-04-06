import * as React from 'react';

import {
  render,
  RenderResult,
  fireEvent,
  waitFor,
  act,
} from '@testing-library/react';
import axios from 'axios';
import { last, omit } from 'ramda';

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
import { toRawFilter, toFilter } from '../api/adapters';
import { newFilter } from '../models';
import { filterEndpoint } from '../api';

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

const retrievedCustomFilters = {
  meta: {
    limit: 30,
    page: 1,
    total: 1,
  },
  result: [
    {
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
              name: 'Unhandled problems',
            },
          ],
        },
        {
          name: 'statuses',
          type: 'multi_select',
          value: [
            {
              id: 'OK',
              name: 'OK',
            },
          ],
        },
        {
          name: 'host_groups',
          object_type: 'host_group',
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
          object_type: 'service_group',
          type: 'multi_select',
          value: [
            {
              id: 0,
              name: 'Web-services',
            },
          ],
        },
        {
          name: 'search',
          type: 'text',
          value: 'my search',
        },
      ],
      id: 0,
      name: 'MyFilter',
    },
  ],
};

const [createdFilter] = retrievedCustomFilters.result;

describe(SaveMenu, () => {
  beforeEach(() => {
    mockedAxios.get.mockResolvedValue({ data: retrievedCustomFilters });
    mockedAxios.put.mockResolvedValue({ data: {} });
    mockedAxios.post.mockResolvedValue({ data: createdFilter });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
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

    act(() => {
      filterState.setFilter(newFilter);
      filterState.setNextSearch('toto');
    });

    expect(
      last(getAllByText(labelSave))?.parentElement?.parentElement,
    ).toHaveAttribute('aria-disabled', 'true');

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
        omit(
          ['id'],
          toRawFilter({ ...filterState.updatedFilter, name: 'My new filter' }),
        ),
        expect.anything(),
      );
    });
  });

  it('sends an updateFilter request when the "Save" command is clicked', async () => {
    const { getAllByText } = renderSaveMenu();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    const newSearch = 'new search';

    const updatedFilter = toRawFilter({
      ...toFilter(createdFilter),
      criterias: {
        ...toFilter(createdFilter).criterias,
        search: newSearch,
      },
    });

    mockedAxios.put.mockResolvedValue({ data: updatedFilter });

    act(() => {
      filterState.setFilter(newFilter);
      filterState.setNextSearch(newSearch);
    });

    expect(
      last(getAllByText(labelSave))?.parentElement?.parentElement,
    ).toHaveAttribute('aria-disabled', 'true');

    fireEvent.click(last(getAllByText(labelSave)) as HTMLElement);

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        `${filterEndpoint}/${filterState.updatedFilter.id}`,
        omit(['id'], toRawFilter(filterState.updatedFilter)),
        expect.anything(),
      );
    });
  });
});
