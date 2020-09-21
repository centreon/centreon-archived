import * as React from 'react';

import axios from 'axios';

import { render, waitFor, fireEvent } from '@testing-library/react';
import { Provider } from 'react-redux';
import { BrowserRouter } from 'react-router-dom';

import createStore from '../../../store';
import { setRefreshIntervals } from '../../../redux/actions/refreshActions';

import HostMenu from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;
describe(HostMenu, () => {
  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  it('redirects to the Resource status page with the resource type filter set to host and the corresponding status when a status chip is clicked', async () => {
    const store = createStore();

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        down: {
          total: 3,
          unhandled: 3,
        },
        unreachable: {
          total: 2,
          unhandled: 2,
        },
        ok: 1,
        pending: 0,
        total: 5,
        refreshTime: 15,
      },
    });

    const { getByText, getAllByText } = render(
      <BrowserRouter>
        <Provider store={store}>
          <HostMenu />
        </Provider>
      </BrowserRouter>,
    );

    store.dispatch(setRefreshIntervals({ AjaxTimeReloadMonitoring: 10 }));

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    fireEvent.click(getByText('3'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":{"resourceTypes":[{"id":"host"}],"statuses":[{"id":"DOWN"}],"states":[{"id":"unhandled_problems"}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('2'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":{"resourceTypes":[{"id":"host"}],"statuses":[{"id":"UNREACHABLE"}],"states":[{"id":"unhandled_problems"}]}}&fromTopCounter=true',
    );

    fireEvent.click(getAllByText('1')[0]);
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":{"resourceTypes":[{"id":"host"}],"statuses":[{"id":"UP"}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('All'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":{"resourceTypes":[{"id":"host"}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Down'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":{"resourceTypes":[{"id":"host"}],"statuses":[{"id":"DOWN"}],"states":[{"id":"unhandled_problems"}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Unreachable'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":{"resourceTypes":[{"id":"host"}],"statuses":[{"id":"UNREACHABLE"}],"states":[{"id":"unhandled_problems"}]}}&fromTopCounter=true',
    );
    fireEvent.click(getByText('Up'));

    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":{"resourceTypes":[{"id":"host"}],"statuses":[{"id":"UP"}]}}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Pending'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":{"resourceTypes":[{"id":"host"}],"statuses":[{"id":"PENDING"}]}}&fromTopCounter=true',
    );
  });
});
