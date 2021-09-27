import * as React from 'react';

import axios from 'axios';
import { render, waitFor, fireEvent } from '@testing-library/react';
import { Provider } from 'react-redux';
import { BrowserRouter } from 'react-router-dom';

import createStore from '../../../store';

import ServiceMenu from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;
describe(ServiceMenu, () => {
  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  it('redirects to the Resource status page with the resource type filter set to service and the corresponding status when a status chip is clicked', async () => {
    const store = createStore();

    mockedAxios.get.mockResolvedValue({
      data: {
        critical: {
          total: 4,
          unhandled: 4,
        },
        ok: 1,
        pending: 0,
        refreshTime: 15,
        total: 9,
        unknown: {
          total: 2,
          unhandled: 2,
        },
        warning: {
          total: 3,
          unhandled: 3,
        },
      },
    });

    const { getByText, getAllByText } = render(
      <BrowserRouter>
        <Provider store={store}>
          <ServiceMenu />
        </Provider>
      </BrowserRouter>,
    );

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    fireEvent.click(getByText('4'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"service","name":"Service"}]},{"name":"statuses","value":[{"id":"CRITICAL","name":"Critical"}]},{"name":"states","value":[{"id":"unhandled_problems","name":"Unhandled"}]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('3'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"service","name":"Service"}]},{"name":"statuses","value":[{"id":"WARNING","name":"Warning"}]},{"name":"states","value":[{"id":"unhandled_problems","name":"Unhandled"}]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('2'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"service","name":"Service"}]},{"name":"statuses","value":[{"id":"UNKNOWN","name":"Unknown"}]},{"name":"states","value":[{"id":"unhandled_problems","name":"Unhandled"}]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getAllByText('1')[0]);
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"service","name":"Service"}]},{"name":"statuses","value":[{"id":"OK","name":"Ok"}]},{"name":"states","value":[]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('All'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"service","name":"Service"}]},{"name":"statuses","value":[]},{"name":"states","value":[]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Critical'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"service","name":"Service"}]},{"name":"statuses","value":[{"id":"CRITICAL","name":"Critical"}]},{"name":"states","value":[{"id":"unhandled_problems","name":"Unhandled"}]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Warning'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"service","name":"Service"}]},{"name":"statuses","value":[{"id":"WARNING","name":"Warning"}]},{"name":"states","value":[{"id":"unhandled_problems","name":"Unhandled"}]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Unknown'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"service","name":"Service"}]},{"name":"statuses","value":[{"id":"UNKNOWN","name":"Unknown"}]},{"name":"states","value":[{"id":"unhandled_problems","name":"Unhandled"}]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Ok'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"service","name":"Service"}]},{"name":"statuses","value":[{"id":"OK","name":"Ok"}]},{"name":"states","value":[]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Pending'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"service","name":"Service"}]},{"name":"statuses","value":[{"id":"PENDING","name":"Pending"}]},{"name":"states","value":[]},{"name":"search","value":""}]}&fromTopCounter=true',
    );
  });
});
