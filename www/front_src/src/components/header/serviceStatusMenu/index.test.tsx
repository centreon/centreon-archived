import * as React from 'react';

import axios from 'axios';

import { render, waitFor, fireEvent } from '@testing-library/react';
import { Provider } from 'react-redux';
import { BrowserRouter } from 'react-router-dom';

import createStore from '../../../store';
import { setRefreshIntervals } from '../../../redux/actions/refreshActions';

import ServiceMenu from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;
describe(ServiceMenu, () => {
  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  it('redirects to the Resource status page with the resource type filter set to service and the corresponding status when a status chip is clicked', async () => {
    const store = createStore();

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        critical: {
          total: 4,
          unhandled: 4,
        },
        warning: {
          total: 3,
          unhandled: 3,
        },
        unknown: {
          total: 2,
          unhandled: 2,
        },
        ok: 1,
        pending: 0,
        total: 9,
        refreshTime: 15,
      },
    });

    const { getByText, getAllByText } = render(
      <BrowserRouter>
        <Provider store={store}>
          <ServiceMenu />
        </Provider>
      </BrowserRouter>,
    );

    store.dispatch(setRefreshIntervals({ AjaxTimeReloadMonitoring: 10 }));

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    fireEvent.click(getByText('4'));
    expect(window.location.href).toBe(
      'http://localhost/monitoring/resources?filter={%22criterias%22:{%22resourceTypes%22:[{%22id%22:%22service%22}],%22statuses%22:[{%22id%22:%22CRITICAL%22}],%22states%22:[{%22id%22:%22unhanlded_problems%22}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('3'));
    expect(window.location.href).toBe(
      'http://localhost/monitoring/resources?filter={%22criterias%22:{%22resourceTypes%22:[{%22id%22:%22service%22}],%22statuses%22:[{%22id%22:%22WARNING%22}],%22states%22:[{%22id%22:%22unhanlded_problems%22}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('2'));
    expect(window.location.href).toBe(
      'http://localhost/monitoring/resources?filter={%22criterias%22:{%22resourceTypes%22:[{%22id%22:%22service%22}],%22statuses%22:[{%22id%22:%22UNKNOWN%22}],%22states%22:[{%22id%22:%22unhanlded_problems%22}]}}&fromTopCounter=true',
    );

    fireEvent.click(getAllByText('1')[0]);
    expect(window.location.href).toBe(
      'http://localhost/monitoring/resources?filter={%22criterias%22:{%22resourceTypes%22:[{%22id%22:%22service%22}],%22statuses%22:[{%22id%22:%22OK%22}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('All'));
    expect(window.location.href).toBe(
      'http://localhost/monitoring/resources?filter={%22criterias%22:{%22resourceTypes%22:[{%22id%22:%22service%22}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Critical'));
    expect(window.location.href).toBe(
      'http://localhost/monitoring/resources?filter={%22criterias%22:{%22resourceTypes%22:[{%22id%22:%22service%22}],%22statuses%22:[{%22id%22:%22CRITICAL%22}],%22states%22:[{%22id%22:%22unhanlded_problems%22}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Warning'));
    expect(window.location.href).toBe(
      'http://localhost/monitoring/resources?filter={%22criterias%22:{%22resourceTypes%22:[{%22id%22:%22service%22}],%22statuses%22:[{%22id%22:%22WARNING%22}],%22states%22:[{%22id%22:%22unhanlded_problems%22}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Unknown'));
    expect(window.location.href).toBe(
      'http://localhost/monitoring/resources?filter={%22criterias%22:{%22resourceTypes%22:[{%22id%22:%22service%22}],%22statuses%22:[{%22id%22:%22UNKNOWN%22}],%22states%22:[{%22id%22:%22unhanlded_problems%22}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Ok'));
    expect(window.location.href).toBe(
      'http://localhost/monitoring/resources?filter={%22criterias%22:{%22resourceTypes%22:[{%22id%22:%22service%22}],%22statuses%22:[{%22id%22:%22OK%22}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Pending'));
    expect(window.location.href).toBe(
      'http://localhost/monitoring/resources?filter={%22criterias%22:{%22resourceTypes%22:[{%22id%22:%22service%22}],%22statuses%22:[{%22id%22:%22PENDING%22}]}}&fromTopCounter=true',
    );
  });
});
