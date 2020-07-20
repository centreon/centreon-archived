import * as React from 'react';

import axios from 'axios';

import {
  RenderResult,
  render,
  waitFor,
  fireEvent,
  act,
} from '@testing-library/react';
import { omit } from 'ramda';
import EditFilterPanel from '.';
import Context, { ResourceContext } from '../../Context';
import useFilter from '../useFilter';
import { labelFilter, labelName, labelDelete } from '../../translatedLabels';
import { filterEndpoint } from '../api';

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
  result: [
    {
      id: 0,
      name: 'MyFilter',
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
          name: 'service_groups',
          type: 'multi_select',
          value: [],
          object_type: 'service_groups',
        },
        {
          name: 'host_groups',
          type: 'multi_select',
          value: [],
          object_type: 'host_groups',
        },
        {
          name: 'search',
          type: 'text',
          value: undefined,
        },
      ],
    },
  ],
  meta: {
    page: 1,
    limit: 30,
    total: 1,
  },
};

const [customFilter] = retrievedCustomFilters.result;

const renderEditFilterPanel = (): RenderResult =>
  render(<EditFilterPanelTest />);

describe(EditFilterPanel, () => {
  beforeEach(() => {
    mockedAxios.get.mockResolvedValue({ data: retrievedCustomFilters });
    mockedAxios.put.mockResolvedValue({ data: {} });
    mockedAxios.delete.mockResolvedValue({ data: {} });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.put.mockReset();
    mockedAxios.delete.mockReset();
  });

  it('sends an update request when a filter input is changed and the enter key is pressed', async () => {
    const { getByLabelText } = renderEditFilterPanel();

    act(() => {
      filterState.loadCustomFilters();
      filterState.setEditPanelOpen(true);
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    const newName = 'New name';

    const renameFilterInput = getByLabelText(
      `${labelFilter}-${customFilter.id}-${labelName}`,
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
      expect(mockedAxios.put).toHaveBeenCalledWith(
        `${filterEndpoint}/${customFilter.id}`,
        omit(['id'], { ...customFilter, name: newName }),
        expect.anything(),
      );
      expect(mockedAxios.get).toHaveBeenCalled();
    });
  });

  it('sends a delete request when a filter delete button is clicked', async () => {
    const { getByTitle, getByText } = renderEditFilterPanel();

    act(() => {
      filterState.loadCustomFilters();
      filterState.setEditPanelOpen(true);
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    fireEvent.click(getByTitle(labelDelete).firstElementChild as HTMLElement);
    fireEvent.click(getByText(labelDelete).parentElement as HTMLElement);

    await waitFor(() => {
      expect(mockedAxios.delete).toHaveBeenCalledWith(
        `${filterEndpoint}/${customFilter.id}`,
        expect.anything(),
      );
      expect(mockedAxios.get).toHaveBeenCalled();
    });
  });
});
