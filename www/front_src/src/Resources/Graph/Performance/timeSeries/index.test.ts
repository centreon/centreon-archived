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
        },
        metric: 'time',
        unit: 'ms',
        legend: 'Time (ms)',
      },
    ],
    times: ['2020-11-05T10:35:00Z', '2020-11-05T10:40:00Z'],
  };

  describe('getTimeSeries', () => {
    it('returns the time series for the given graph data', () => {
      expect(timeSeries.getTimeSeries(graphData)).toEqual([
        { rta: 0, time: 0.5, timeTick: '2020-11-05T10:35:00Z' },
        { rta: 1, time: 3, timeTick: '2020-11-05T10:40:00Z' },
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
        { time: 0.5, timeTick: '2020-11-05T10:35:00Z' },
        { rta: 1, time: 3, timeTick: '2020-11-05T10:40:00Z' },
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
          lineColor: 'black',
          metric: 'rta',
          name: 'Round-Trip-Time Average (ms)',
          transparency: 80,
          unit: 'ms',
        },
        {
          areaColor: 'blue',
          color: 'blue',
          display: true,
          filled: true,
          highlight: undefined,
          lineColor: 'blue',
          metric: 'time',
          name: 'Time (ms)',
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
      ).toEqual([0, 1, 0.5, 3]);
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
        lineColor: 'black',
        metric: 'rta',
        name: 'Round-Trip-Time Average (ms)',
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
      ).toEqual([0, 1, 0.5, 3]);
    });
  });
});
