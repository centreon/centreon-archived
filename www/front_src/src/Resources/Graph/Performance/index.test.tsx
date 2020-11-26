import * as React from 'react';

import axios from 'axios';
import { render, screen } from '@testing-library/react';

import PerformanceGraph from '.';
import { labelComment } from '../../translatedLabels';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const graphData = {
  global: {},
  metrics: [
    {
      data: [0, 1],
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
      data: [0.5, 3],
      ds_data: {
        ds_color_line: '#000',
        ds_filled: true,
        ds_color_area: '#001',
        ds_transparency: 80,
      },
      metric: 'time',
      unit: 'ms',
      legend: 'Time (ms)',
    },
  ],
  times: ['2020-11-05T10:35:00Z', '2020-11-05T10:40:00Z'],
};

const timeline = {
  result: [
    {
      type: 'comment',
      id: 5,
      date: '2020-11-05T10:35:00Z',
      author_name: 'admin',
      content: 'Plop',
    },
    {
      type: 'comment',
      id: 5,
      date: '2020-11-05T10:40:00Z',
      author_name: 'admin',
      content: 'Plop',
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

  it('displays comment annotations', async () => {
    const endpoint = 'endpoint';
    const graphHeight = 200;
    const timelineEndpoint = 'timeline';

    render(
      <PerformanceGraph
        endpoint={endpoint}
        graphHeight={graphHeight}
        timelineEndpoint={timelineEndpoint}
      />,
    );

    const annotations = await screen.findAllByLabelText(labelComment);

    expect(annotations).toHaveLength(2);
  });
});
