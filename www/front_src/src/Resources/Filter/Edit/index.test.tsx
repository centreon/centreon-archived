import * as React from 'react';

import axios from 'axios';
import {
  RenderResult,
  render,
  waitFor,
  fireEvent,
  act,
} from '@testing-library/react';
import { omit, head, prop } from 'ramda';
import { makeDnd, DND_DIRECTION_DOWN } from 'react-beautiful-dnd-test-utils';

import Context, { ResourceContext } from '../../Context';
import useFilter from '../useFilter';
import { labelFilter, labelName, labelDelete } from '../../translatedLabels';
import { filterEndpoint } from '../api';
import { defaultSortField, defaultSortOrder } from '../../Listing/columns';

import EditFilterPanel from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

let filterState;

const EditFilterPanelTest = (): JSX.Element => {
  filterState = useFilter();

  return (
    <Context.Provider
      value={
        {
          ...filterState,
        } as ResourceContext
      }
    >
      <EditFilterPanel />
    </Context.Provider>
  );
};

const retrievedCustomFilters = {
  result: [0, 1].map((index) => ({
    id: index,
    name: `My filter ${index}`,
    criterias: [
      {
        name: 'resource_types',
        type: 'multi_select',
        value: [],
      },
      {
        name: 'states',
        type: 'multi_select',
        value: [],
      },
      {
        name: 'statuses',
        type: 'multi_select',
        value: [],
      },
      {
        name: 'host_groups',
        type: 'multi_select',
        value: [],
        object_type: 'host_groups',
      },
      {
        name: 'service_groups',
        type: 'multi_select',
        value: [],
        object_type: 'service_groups',
      },
      {
        name: 'search',
        type: 'text',
        value: '',
      },
      {
        name: 'sort',
        type: 'array',
        value: [defaultSortField, defaultSortOrder],
      },
    ],
  })),
  meta: {
    page: 1,
    limit: 30,
    total: 1,
  },
};

const renderEditFilterPanel = (): RenderResult =>
  render(<EditFilterPanelTest />);

describe(EditFilterPanel, () => {
  beforeEach(() => {
    mockedAxios.get.mockResolvedValue({ data: retrievedCustomFilters });
    mockedAxios.put.mockResolvedValue({ data: {} });
    mockedAxios.patch.mockResolvedValue({ data: {} });
    mockedAxios.delete.mockResolvedValue({ data: {} });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.put.mockReset();
    mockedAxios.patch.mockReset();
    mockedAxios.delete.mockReset();
  });

  it('renames the filter and sends an update request the corresponding input is changed and the enter key is pressed', async () => {
    const { getByLabelText } = renderEditFilterPanel();

    const [firstFilter] = retrievedCustomFilters.result;

    act(() => {
      filterState.loadCustomFilters();
      filterState.setEditPanelOpen(true);
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    const newName = 'New name';
    const updatedFilter = { ...firstFilter, name: newName };

    mockedAxios.put.mockResolvedValue({ data: updatedFilter });

    const renameFilterInput = getByLabelText(
      `${labelFilter}-${firstFilter.id}-${labelName}`,
    );

    fireEvent.change(renameFilterInput, {
      target: {
        value: newName,
      },
    });

    fireEvent.keyDown(renameFilterInput, {
      keyCode: 13,
    });

    await waitFor(() => {
      expect(filterState.customFilters[0].name).toEqual(newName);
      expect(mockedAxios.put).toHaveBeenCalledWith(
        `${filterEndpoint}/${firstFilter.id}`,
        omit(['id'], { ...firstFilter, name: newName }),
        expect.anything(),
      );
    });
  });

  it('deletes a filter and sends a delete request when the corresponding delete button is clicked', async () => {
    const { getAllByTitle, getByText } = renderEditFilterPanel();

    const [firstFilter] = retrievedCustomFilters.result;

    act(() => {
      filterState.loadCustomFilters();
      filterState.setEditPanelOpen(true);
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    fireEvent.click(
      head(getAllByTitle(labelDelete))?.firstElementChild as HTMLElement,
    );
    fireEvent.click(getByText(labelDelete).parentElement as HTMLElement);

    await waitFor(() => {
      expect(filterState.customFilters.map(prop('id'))).not.toContain(
        firstFilter.id,
      );
      expect(mockedAxios.delete).toHaveBeenCalledWith(
        `${filterEndpoint}/${firstFilter.id}`,
        expect.anything(),
      );
    });
  });

  it('reorders the filter and sends a reorder request when it is dragged to a different position', async () => {
    const [firstFilter] = retrievedCustomFilters.result;

    const { getByText, container } = renderEditFilterPanel();

    act(() => {
      filterState.loadCustomFilters();
      filterState.setEditPanelOpen(true);
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    const firstFilterDraggable = container.querySelector(
      `[data-rbd-drag-handle-draggable-id="${firstFilter.id}"]`,
    );

    await makeDnd({
      getByText,
      getDragEl: () => firstFilterDraggable,
      direction: DND_DIRECTION_DOWN,
      positions: 1,
    });

    await waitFor(() => {
      expect(filterState.customFilters.map(prop('id'))).toEqual([1, 0]);
      expect(mockedAxios.patch).toHaveBeenCalledWith(
        `${filterEndpoint}/${firstFilter.id}`,
        { order: 1 },
        expect.anything(),
      );
    });
  });
});
