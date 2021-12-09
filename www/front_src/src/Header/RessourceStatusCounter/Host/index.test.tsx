import * as React from 'react';

import axios from 'axios';
import { render, waitFor, fireEvent } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';

import HostStatusCounter from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

describe(HostStatusCounter, () => {
  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  it('redirects to the Resource status page with the resource type filter set to host and the corresponding status when a status chip is clicked', async () => {
    mockedAxios.get.mockResolvedValue({
      data: {
        down: {
          total: 3,
          unhandled: 3,
        },
        ok: 1,
        pending: 0,
        refreshTime: 15,
        total: 5,
        unreachable: {
          total: 2,
          unhandled: 2,
        },
      },
    });

    const { getByText, getAllByText } = render(
      <BrowserRouter>
        <HostStatusCounter />
      </BrowserRouter>,
    );

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    fireEvent.click(getByText('3'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"host","name":"Host"}]},{"name":"statuses","value":[{"id":"DOWN","name":"Down"}]},{"name":"states","value":[{"id":"unhandled_problems","name":"Unhandled"}]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('2'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"host","name":"Host"}]},{"name":"statuses","value":[{"id":"UNREACHABLE","name":"Unreachable"}]},{"name":"states","value":[{"id":"unhandled_problems","name":"Unhandled"}]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getAllByText('1')[0]);
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"host","name":"Host"}]},{"name":"statuses","value":[{"id":"UP","name":"Up"}]},{"name":"states","value":[]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('All'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"host","name":"Host"}]},{"name":"statuses","value":[]},{"name":"states","value":[]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Down'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"host","name":"Host"}]},{"name":"statuses","value":[{"id":"DOWN","name":"Down"}]},{"name":"states","value":[{"id":"unhandled_problems","name":"Unhandled"}]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Unreachable'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"host","name":"Host"}]},{"name":"statuses","value":[{"id":"UNREACHABLE","name":"Unreachable"}]},{"name":"states","value":[{"id":"unhandled_problems","name":"Unhandled"}]},{"name":"search","value":""}]}&fromTopCounter=true',
    );
    fireEvent.click(getByText('Up'));

    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"host","name":"Host"}]},{"name":"statuses","value":[{"id":"UP","name":"Up"}]},{"name":"states","value":[]},{"name":"search","value":""}]}&fromTopCounter=true',
    );

    fireEvent.click(getByText('Pending'));
    expect(decodeURI(window.location.href)).toBe(
      'http://localhost/monitoring/resources?filter={"criterias":[{"name":"resource_types","value":[{"id":"host","name":"Host"}]},{"name":"statuses","value":[{"id":"PENDING","name":"Pending"}]},{"name":"states","value":[]},{"name":"search","value":""}]}&fromTopCounter=true',
    );
  });
});
