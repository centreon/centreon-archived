import { GraphData } from '../models';

import * as timeSeries from '.';

describe('timeSeries', () => {
  const graphData: GraphData = {
    global: {},
    metrics: [
      {
        average_value: 1,
        data: [0, 1],
        ds_data: {
          ds_color_area: 'transparent',
          ds_color_line: 'black',
          ds_filled: false,
          ds_invert: null,
          ds_legend: 'Round-Trip-Time Average',
          ds_order: null,
          ds_stack: null,
          ds_transparency: 80
        },
        legend: 'Round-Trip-Time Average (ms)',
        maximum_value: 1.5,
        metric: 'rta',
        minimum_value: 0.5,
        unit: 'ms'
      },
      {
        average_value: 1,
        data: [0.5, 3],
        ds_data: {
          ds_color_area: 'blue',
          ds_color_line: 'blue',
          ds_filled: true,
          ds_invert: null,
          ds_legend: 'Time',
          ds_order: null,
          ds_stack: null,
          ds_transparency: 80
        },
        legend: 'Time (ms)',
        maximum_value: 1.5,
        metric: 'time',
        minimum_value: 0.5,
        unit: 'ms'
      },
      {
        average_value: 1,
        data: [6, 4],
        ds_data: {
          ds_color_area: 'red',
          ds_color_line: 'red',
          ds_filled: true,
          ds_invert: null,
          ds_legend: 'Average duration',
          ds_order: '2',
          ds_stack: '1',
          ds_transparency: 80
        },
        legend: 'Average duration (ms)',
        maximum_value: 1.5,
        metric: 'avgDuration',
        minimum_value: 0.5,
        unit: 'ms'
      },
      {
        average_value: 1,
        data: [12, 25],
        ds_data: {
          ds_color_area: 'yellow',
          ds_color_line: 'yellow',
          ds_filled: true,
          ds_invert: '1',
          ds_legend: 'Duration',
          ds_order: '1',
          ds_stack: '1',
          ds_transparency: 80
        },
        legend: 'Duration (ms)',
        maximum_value: 1.5,
        metric: 'duration',
        minimum_value: 0.5,
        unit: 'ms'
      },
      {
        average_value: 1,
        data: [0, 1],
        ds_data: {
          ds_color_area: 'yellow',
          ds_color_line: 'yellow',
          ds_filled: true,
          ds_invert: null,
          ds_legend: 'Packet Loss',
          ds_order: null,
          ds_stack: null,
          ds_transparency: 80
        },
        legend: 'Packet Loss (%)',
        maximum_value: 1.5,
        metric: 'packet_loss',
        minimum_value: 0.5,
        unit: '%'
      }
    ],
    times: ['2020-11-05T10:35:00Z', '2020-11-05T10:40:00Z']
  };

  describe('getTimeSeries', () => {
    it('returns the time series for the given graph data', () => {
      expect(timeSeries.getTimeSeries(graphData)).toEqual([
        {
          avgDuration: 6,
          duration: 12,
          packet_loss: 0,
          rta: 0,
          time: 0.5,
          timeTick: '2020-11-05T10:35:00Z'
        },
        {
          avgDuration: 4,
          duration: 25,
          packet_loss: 1,
          rta: 1,
          time: 3,
          timeTick: '2020-11-05T10:40:00Z'
        }
      ]);
    });

    it('filters metric values below the given lower-limit value', () => {
      const graphDataWithLowerLimit = {
        ...graphData,
        global: {
          'lower-limit': 0.4
        }
      };

      expect(timeSeries.getTimeSeries(graphDataWithLowerLimit)).toEqual([
        {
          avgDuration: 6,
          duration: 12,
          time: 0.5,
          timeTick: '2020-11-05T10:35:00Z'
        },
        {
          avgDuration: 4,
          duration: 25,
          packet_loss: 1,
          rta: 1,
          time: 3,
          timeTick: '2020-11-05T10:40:00Z'
        }
      ]);
    });
  });

  describe('getLineData', () => {
    it('returns the line information for the given graph data', () => {
      expect(timeSeries.getLineData(graphData)).toEqual([
        {
          areaColor: 'transparent',
          average_value: 1,
          color: 'black',
          display: true,
          filled: false,
          highlight: undefined,
          invert: null,
          legend: 'Round-Trip-Time Average',
          lineColor: 'black',
          maximum_value: 1.5,
          metric: 'rta',
          minimum_value: 0.5,
          name: 'Round-Trip-Time Average (ms)',
          stackOrder: null,
          transparency: 80,
          unit: 'ms'
        },
        {
          areaColor: 'blue',
          average_value: 1,
          color: 'blue',
          display: true,
          filled: true,
          highlight: undefined,
          invert: null,
          legend: 'Time',
          lineColor: 'blue',
          maximum_value: 1.5,
          metric: 'time',
          minimum_value: 0.5,
          name: 'Time (ms)',
          stackOrder: null,
          transparency: 80,
          unit: 'ms'
        },
        {
          areaColor: 'red',
          average_value: 1,
          color: 'red',
          display: true,
          filled: true,
          highlight: undefined,
          invert: null,
          legend: 'Average duration',
          lineColor: 'red',
          maximum_value: 1.5,
          metric: 'avgDuration',
          minimum_value: 0.5,
          name: 'Average duration (ms)',
          stackOrder: 2,
          transparency: 80,
          unit: 'ms'
        },
        {
          areaColor: 'yellow',
          average_value: 1,
          color: 'yellow',
          display: true,
          filled: true,
          highlight: undefined,
          invert: '1',
          legend: 'Duration',
          lineColor: 'yellow',
          maximum_value: 1.5,
          metric: 'duration',
          minimum_value: 0.5,
          name: 'Duration (ms)',
          stackOrder: 1,
          transparency: 80,
          unit: 'ms'
        },
        {
          areaColor: 'yellow',
          average_value: 1,
          color: 'yellow',
          display: true,
          filled: true,
          highlight: undefined,
          invert: null,
          legend: 'Packet Loss',
          lineColor: 'yellow',
          maximum_value: 1.5,
          metric: 'packet_loss',
          minimum_value: 0.5,
          name: 'Packet Loss (%)',
          stackOrder: null,
          transparency: 80,
          unit: '%'
        }
      ]);
    });
  });

  describe('getMetrics', () => {
    it('returns the metrics for the given time value', () => {
      expect(
        timeSeries.getMetrics({
          rta: 1,
          time: 0,
          timeTick: '2020-11-05T10:40:00Z'
        })
      ).toEqual(['rta', 'time']);
    });
  });

  describe('getMetricValuesForUnit', () => {
    it('returns the values in the given time series corresponding to the given line unit', () => {
      const series = timeSeries.getTimeSeries(graphData);
      const lines = timeSeries.getLineData(graphData);
      const unit = 'ms';

      expect(
        timeSeries.getMetricValuesForUnit({ lines, timeSeries: series, unit })
      ).toEqual([0, 1, 0.5, 3, 6, 4, 12, 25]);
    });
  });

  describe('getUnits', () => {
    it('returns the units for the given lines', () => {
      const lines = timeSeries.getLineData(graphData);

      expect(timeSeries.getUnits(lines)).toEqual(['ms', '%']);
    });
  });

  describe('getDates', () => {
    it('teruns the dates for the given time series', () => {
      const series = timeSeries.getTimeSeries(graphData);

      expect(timeSeries.getDates(series)).toEqual([
        new Date('2020-11-05T10:35:00.000Z'),
        new Date('2020-11-05T10:40:00.000Z')
      ]);
    });
  });

  describe('getLineForMetric', () => {
    it('returns the line corresponding to the given metrics', () => {
      const lines = timeSeries.getLineData(graphData);

      expect(timeSeries.getLineForMetric({ lines, metric: 'rta' })).toEqual({
        areaColor: 'transparent',
        average_value: 1,
        color: 'black',
        display: true,
        filled: false,
        highlight: undefined,
        invert: null,
        legend: 'Round-Trip-Time Average',
        lineColor: 'black',
        maximum_value: 1.5,
        metric: 'rta',
        minimum_value: 0.5,
        name: 'Round-Trip-Time Average (ms)',
        stackOrder: null,
        transparency: 80,
        unit: 'ms'
      });
    });
  });

  describe('getMetricValuesForLines', () => {
    it('returns the metric values for the given lines within the given time series', () => {
      const lines = timeSeries.getLineData(graphData);
      const series = timeSeries.getTimeSeries(graphData);

      expect(
        timeSeries.getMetricValuesForLines({ lines, timeSeries: series })
      ).toEqual([0, 1, 0.5, 3, 6, 4, 12, 25, 0, 1]);
    });
  });

  describe('getSortedStackedLines', () => {
    it('returns stacked lines sorted by their own order for the given lines', () => {
      const lines = timeSeries.getLineData(graphData);

      expect(timeSeries.getSortedStackedLines(lines)).toEqual([
        {
          areaColor: 'yellow',
          average_value: 1,
          color: 'yellow',
          display: true,
          filled: true,
          highlight: undefined,
          invert: '1',
          legend: 'Duration',
          lineColor: 'yellow',
          maximum_value: 1.5,
          metric: 'duration',
          minimum_value: 0.5,
          name: 'Duration (ms)',
          stackOrder: 1,
          transparency: 80,
          unit: 'ms'
        },
        {
          areaColor: 'red',
          average_value: 1,
          color: 'red',
          display: true,
          filled: true,
          highlight: undefined,
          invert: null,
          legend: 'Average duration',
          lineColor: 'red',
          maximum_value: 1.5,
          metric: 'avgDuration',
          minimum_value: 0.5,
          name: 'Average duration (ms)',
          stackOrder: 2,
          transparency: 80,
          unit: 'ms'
        }
      ]);
    });
  });

  describe('getStackedMetricValues', () => {
    it('returns stacked metrics values for the given lines and the given time series', () => {
      const lines = timeSeries.getLineData(graphData);
      const series = timeSeries.getTimeSeries(graphData);

      expect(
        timeSeries.getStackedMetricValues({
          lines: timeSeries.getSortedStackedLines(lines),
          timeSeries: series
        })
      ).toEqual([18, 29]);
    });
  });

  describe('getTimeSeriesForLines', () => {
    it('returns the specific time series for the given lines and the fiven time series', () => {
      const lines = timeSeries.getLineData(graphData);
      const series = timeSeries.getTimeSeries(graphData);

      expect(
        timeSeries.getTimeSeriesForLines({
          lines: timeSeries.getSortedStackedLines(lines),
          timeSeries: series
        })
      ).toEqual([
        {
          avgDuration: 6,
          duration: 12,
          timeTick: '2020-11-05T10:35:00Z'
        },
        {
          avgDuration: 4,
          duration: 25,
          timeTick: '2020-11-05T10:40:00Z'
        }
      ]);
    });
  });

  describe('getInvertedStackedLines', () => {
    it('returns inverted and stacked lines for the given lines', () => {
      const lines = timeSeries.getLineData(graphData);

      expect(timeSeries.getInvertedStackedLines(lines)).toEqual([
        {
          areaColor: 'yellow',
          average_value: 1,
          color: 'yellow',
          display: true,
          filled: true,
          highlight: undefined,
          invert: '1',
          legend: 'Duration',
          lineColor: 'yellow',
          maximum_value: 1.5,
          metric: 'duration',
          minimum_value: 0.5,
          name: 'Duration (ms)',
          stackOrder: 1,
          transparency: 80,
          unit: 'ms'
        }
      ]);
    });
  });

  describe('getNotInvertedStackedLines', () => {
    it('returns not inverted and stacked lines for the given lines', () => {
      const lines = timeSeries.getLineData(graphData);

      expect(timeSeries.getNotInvertedStackedLines(lines)).toEqual([
        {
          areaColor: 'red',
          average_value: 1,
          color: 'red',
          display: true,
          filled: true,
          highlight: undefined,
          invert: null,
          legend: 'Average duration',
          lineColor: 'red',
          maximum_value: 1.5,
          metric: 'avgDuration',
          minimum_value: 0.5,
          name: 'Average duration (ms)',
          stackOrder: 2,
          transparency: 80,
          unit: 'ms'
        }
      ]);
    });
  });

  describe('hasUnitStackedLines', () => {
    it('returns true if the given unit contains stacked lines following the given lines, false otherwise', () => {
      const lines = timeSeries.getLineData(graphData);

      expect(timeSeries.hasUnitStackedLines({ lines, unit: 'ms' })).toEqual(
        true
      );

      expect(timeSeries.hasUnitStackedLines({ lines, unit: '%' })).toEqual(
        false
      );
    });
  });
});
