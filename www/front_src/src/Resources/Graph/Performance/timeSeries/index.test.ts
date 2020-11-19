import * as timeSeries from '.';

describe('timeSeries', () => {
  const graphData = {
    global: {},
    metrics: [
      {
        data: [0, 1],
        ds_data: {
          ds_color_line: 'black',
          ds_filled: false,
          ds_color_area: 'transparent',
          ds_transparency: 80,
          ds_stack: null,
          ds_order: null,
          ds_invert: false,
        },
        metric: 'rta',
        unit: 'ms',
        legend: 'Round-Trip-Time Average (ms)',
      },
      {
        data: [0.5, 3],
        ds_data: {
          ds_color_line: 'blue',
          ds_filled: true,
          ds_color_area: 'blue',
          ds_transparency: 80,
          ds_stack: null,
          ds_order: null,
          ds_invert: false,
        },
        metric: 'time',
        unit: 'ms',
        legend: 'Time (ms)',
      },
      {
        data: [6, 4],
        ds_data: {
          ds_color_line: 'red',
          ds_filled: true,
          ds_color_area: 'red',
          ds_transparency: 80,
          ds_stack: '1',
          ds_order: '2',
          ds_invert: false,
        },
        metric: 'avgDuration',
        unit: 'ms',
        legend: 'Average duration (ms)',
      },
      {
        data: [12, 25],
        ds_data: {
          ds_color_line: 'yellow',
          ds_filled: true,
          ds_color_area: 'yellow',
          ds_transparency: 80,
          ds_stack: '1',
          ds_order: '1',
          ds_invert: false,
        },
        metric: 'duration',
        unit: 'ms',
        legend: 'Duration (ms)',
      },
    ],
    times: ['2020-11-05T10:35:00Z', '2020-11-05T10:40:00Z'],
  };

  describe('getTimeSeries', () => {
    it('returns the time series for the given graph data', () => {
      expect(timeSeries.getTimeSeries(graphData)).toEqual([
        {
          duration: 12,
          avgDuration: 6,
          rta: 0,
          time: 0.5,
          timeTick: '2020-11-05T10:35:00Z',
        },
        {
          duration: 25,
          avgDuration: 4,
          rta: 1,
          time: 3,
          timeTick: '2020-11-05T10:40:00Z',
        },
      ]);
    });

    it('filters metric values below the given lower-limit value', () => {
      const graphDataWithLowerLimit = {
        ...graphData,
        global: {
          'lower-limit': 0.4,
        },
      };

      expect(timeSeries.getTimeSeries(graphDataWithLowerLimit)).toEqual([
        {
          duration: 12,
          avgDuration: 6,
          time: 0.5,
          timeTick: '2020-11-05T10:35:00Z',
        },
        {
          duration: 25,
          avgDuration: 4,
          rta: 1,
          time: 3,
          timeTick: '2020-11-05T10:40:00Z',
        },
      ]);
    });
  });

  describe('getLineData', () => {
    it('returns the line information for the given graph data', () => {
      expect(timeSeries.getLineData(graphData)).toEqual([
        {
          areaColor: 'transparent',
          color: 'black',
          display: true,
          filled: false,
          highlight: undefined,
          invert: false,
          lineColor: 'black',
          metric: 'rta',
          name: 'Round-Trip-Time Average (ms)',
          stackOrder: null,
          transparency: 80,
          unit: 'ms',
        },
        {
          areaColor: 'blue',
          color: 'blue',
          display: true,
          filled: true,
          highlight: undefined,
          invert: false,
          lineColor: 'blue',
          metric: 'time',
          name: 'Time (ms)',
          stackOrder: null,
          transparency: 80,
          unit: 'ms',
        },
        {
          areaColor: 'red',
          color: 'red',
          display: true,
          filled: true,
          highlight: undefined,
          invert: false,
          lineColor: 'red',
          metric: 'avgDuration',
          name: 'Average duration (ms)',
          stackOrder: 2,
          transparency: 80,
          unit: 'ms',
        },
        {
          areaColor: 'yellow',
          color: 'yellow',
          display: true,
          filled: true,
          highlight: undefined,
          invert: false,
          lineColor: 'yellow',
          metric: 'duration',
          name: 'Duration (ms)',
          stackOrder: 1,
          transparency: 80,
          unit: 'ms',
        },
      ]);
    });
  });

  describe('getMetrics', () => {
    it('returns the metrics for the given time value', () => {
      expect(
        timeSeries.getMetrics({
          timeTick: '2020-11-05T10:40:00Z',
          rta: 1,
          time: 0,
        }),
      ).toEqual(['rta', 'time']);
    });
  });

  describe('getMetricValues', () => {
    it('returns the values for the given time value', () => {
      expect(timeSeries.getMetricValues({ rta: 1, time: 0 })).toEqual([1, 0]);
    });
  });

  describe('getMetricValuesForUnit', () => {
    it('returns the values in the given time series corresponding to the given line unit', () => {
      const series = timeSeries.getTimeSeries(graphData);
      const lines = timeSeries.getLineData(graphData);
      const unit = 'ms';

      expect(
        timeSeries.getMetricValuesForUnit({ timeSeries: series, lines, unit }),
      ).toEqual([0, 1, 0.5, 3, 6, 4, 12, 25]);
    });
  });

  describe('getUnits', () => {
    it('returns the units for the given lines', () => {
      const lines = timeSeries.getLineData(graphData);

      expect(timeSeries.getUnits(lines)).toEqual(['ms']);
    });
  });

  describe('getDates', () => {
    it('teruns the dates for the given time series', () => {
      const series = timeSeries.getTimeSeries(graphData);

      expect(timeSeries.getDates(series)).toEqual([
        new Date('2020-11-05T10:35:00.000Z'),
        new Date('2020-11-05T10:40:00.000Z'),
      ]);
    });
  });

  describe('getLineForMetric', () => {
    it('returns the line corresponding to the given metrics', () => {
      const lines = timeSeries.getLineData(graphData);

      expect(timeSeries.getLineForMetric({ lines, metric: 'rta' })).toEqual({
        areaColor: 'transparent',
        color: 'black',
        display: true,
        filled: false,
        highlight: undefined,
        invert: false,
        lineColor: 'black',
        metric: 'rta',
        name: 'Round-Trip-Time Average (ms)',
        stackOrder: null,
        transparency: 80,
        unit: 'ms',
      });
    });
  });

  describe('getMetricValuesForLines', () => {
    it('returns the metric values for the given lines within the given time series', () => {
      const lines = timeSeries.getLineData(graphData);
      const series = timeSeries.getTimeSeries(graphData);

      expect(
        timeSeries.getMetricValuesForLines({ lines, timeSeries: series }),
      ).toEqual([0, 1, 0.5, 3, 6, 4, 12, 25]);
    });
  });

  describe('getSortedStackedLines', () => {
    it('returns stacked lines sorted by their own order for the given lines', () => {
      const lines = timeSeries.getLineData(graphData);

      expect(timeSeries.getSortedStackedLines(lines)).toEqual([
        {
          areaColor: 'yellow',
          color: 'yellow',
          display: true,
          filled: true,
          highlight: undefined,
          invert: false,
          lineColor: 'yellow',
          metric: 'duration',
          name: 'Duration (ms)',
          stackOrder: 1,
          transparency: 80,
          unit: 'ms',
        },
        {
          areaColor: 'red',
          color: 'red',
          display: true,
          filled: true,
          highlight: undefined,
          invert: false,
          lineColor: 'red',
          metric: 'avgDuration',
          name: 'Average duration (ms)',
          stackOrder: 2,
          transparency: 80,
          unit: 'ms',
        },
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
          timeSeries: series,
        }),
      ).toEqual([18, 29]);
    });
  });

  describe('getSpecificTimeSeries', () => {
    it('returns the specific time series for the given lines and the fiven time series', () => {
      const lines = timeSeries.getLineData(graphData);
      const series = timeSeries.getTimeSeries(graphData);

      expect(
        timeSeries.getSpecificTimeSeries({
          lines: timeSeries.getSortedStackedLines(lines),
          timeSeries: series,
        }),
      ).toEqual([
        {
          duration: 12,
          avgDuration: 6,
          timeTick: '2020-11-05T10:35:00Z',
        },
        {
          duration: 25,
          avgDuration: 4,
          timeTick: '2020-11-05T10:40:00Z',
        },
      ]);
    });
  });
});
