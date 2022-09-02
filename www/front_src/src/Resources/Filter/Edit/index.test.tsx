/* eslint-disable react/jsx-no-constructed-context-values */
import axios from 'axios';
import { omit, head, prop } from 'ramda';
import { Provider } from 'jotai';

import { RenderResult, render, waitFor, fireEvent, act } from '@centreon/ui';

import Context, { ResourceContext } from '../../testUtils/Context';
import useFilter from '../../testUtils/useFilter';
import { labelFilter, labelName, labelDelete } from '../../translatedLabels';
import { filterEndpoint } from '../api';
import { defaultSortField, defaultSortOrder } from '../Criterias/default';

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

const EditFilterPanelTestWithJotai = (): JSX.Element => (
  <Provider>
    <EditFilterPanelTest />
  </Provider>
);

const retrievedCustomFilters = {
  meta: {
    limit: 30,
    page: 1,
    total: 1,
  },
  result: [0, 1].map((index) => ({
    criterias: [
      {
        name: 'resource_types',
        object_type: null,
        type: 'multi_select',
        value: [],
      },
      {
        name: 'states',
        object_type: null,
        type: 'multi_select',
        value: [],
      },
      {
        name: 'statuses',
        object_type: null,
        type: 'multi_select',
        value: [],
      },
      {
        name: 'host_groups',
        object_type: 'host_groups',
        type: 'multi_select',
        value: [],
      },
      {
        name: 'service_groups',
        object_type: 'service_groups',
        type: 'multi_select',
        value: [],
      },
      {
        name: 'search',
        object_type: null,
        type: 'text',
        value: '',
      },
      {
        name: 'sort',
        object_type: null,
        type: 'array',
        value: [defaultSortField, defaultSortOrder],
      },
    ],
    id: index,
    name: `My filter ${index}`,
  })),
};

const renderEditFilterPanel = (): RenderResult =>
  render(<EditFilterPanelTestWithJotai />);

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

    await waitFor(() =>
      expect(
        getByLabelText(`${labelFilter}-${firstFilter.id}-${labelName}`),
      ).toBeInTheDocument(),
    );

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
    const { getAllByLabelText, getByText } = renderEditFilterPanel();

    const [firstFilter] = retrievedCustomFilters.result;

    act(() => {
      filterState.loadCustomFilters();
      filterState.setEditPanelOpen(true);
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    await waitFor(() => expect(getAllByLabelText(labelDelete)).toHaveLength(2));

    fireEvent.click(
      head(getAllByLabelText(labelDelete))?.firstElementChild as HTMLElement,
    );
    fireEvent.click(getByText(labelDelete) as HTMLElement);

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
});
