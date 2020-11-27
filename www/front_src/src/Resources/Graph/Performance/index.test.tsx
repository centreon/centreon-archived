import * as React from 'react';

import axios from 'axios';
import { render, screen, waitFor } from '@testing-library/react';

import userEvent from '@testing-library/user-event';

import { ThemeProvider } from '@centreon/ui';

import PerformanceGraph from '.';
import {
  labelComment,
  labelAcknowledgement,
  labelDowntime,
  labelEventAnnotations,
} from '../../translatedLabels';
import { Resource } from '../../models';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const graphData = {
  global: {},
  metrics: [
    {
      data: [0, 1, 2, 0.5],
      ds_data: {
        ds_color_line: '#fff',
        ds_filled: false,
        ds_color_area: 'transparent',
        ds_transparency: 80,
      },
      metric: 'rta',
      unit: 'ms',
      legend: 'Round-Trip-Time Average (ms)',
    },
    {
      data: [0.5, 3, 1, 3],
      ds_data: {
        ds_color_line: '#000',
        ds_filled: true,
        ds_color_area: '#000',
        ds_transparency: 80,
      },
      metric: 'time',
      unit: 'ms',
      legend: 'Time (ms)',
    },
  ],
  times: [
    '2020-11-05T10:35:00Z',
    '2020-11-05T10:40:00Z',
    '2020-11-05T10:45:00Z',
    '2020-11-05T10:50:00Z',
  ],
};

const timeline = {
  result: [
    {
      type: 'comment',
      id: 5,
      date: '2020-11-05T10:35:00Z',
      contact: {
        name: 'admin',
      },
      content: 'Plop',
    },
    {
      type: 'comment',
      id: 6,
      date: '2020-11-05T10:40:00Z',
      contact: {
        name: 'admin',
      },
      content: 'Plop',
    },
    {
      type: 'acknowledgement',
      id: 7,
      date: '2020-11-05T10:45:00Z',
      content: 'Acknowledged',
      contact: {
        name: 'admin',
      },
    },
    {
      type: 'downtime',
      id: 8,
      date: '2020-11-05T10:45:00Z',
      content: 'Downtime',
      start_date: '2020-11-05T10:35:00Z',
      end_date: '2020-11-05T10:50:00Z',
    },
  ],
  meta: {
    page: 1,
    limit: 10,
    total: 5,
  },
};

describe(PerformanceGraph, () => {
  beforeEach(() => {
    mockedAxios.get
      .mockResolvedValueOnce({ data: graphData })
      .mockResolvedValueOnce({ data: timeline });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  it('displays event annotations when the corresponding switch is active', async () => {
    const endpoint = 'endpoint';
    const graphHeight = 200;
    const timelineEndpoint = 'timeline';

    render(
      <ThemeProvider>
        <PerformanceGraph
          endpoint={endpoint}
          graphHeight={graphHeight}
          timelineEndpoint={timelineEndpoint}
          resource={{} as Resource}
        />
      </ThemeProvider>,
    );

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });

    expect(screen.queryByLabelText(labelComment)).toBeNull();
    expect(screen.queryByLabelText(labelAcknowledgement)).toBeNull();
    expect(screen.queryByLabelText(labelDowntime)).toBeNull();

    userEvent.click(screen.getByText(labelEventAnnotations));

    expect(screen.getAllByLabelText(labelComment)).toHaveLength(2);
    expect(screen.getAllByLabelText(labelAcknowledgement)).toHaveLength(1);
    expect(screen.getAllByLabelText(labelDowntime)).toHaveLength(1);
  });
});
