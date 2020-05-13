import * as React from 'react';

import {
  ComposedChart,
  XAxis,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
} from 'recharts';
import filesize from 'filesize';
import { pipe, map, prop, propEq, find, path, reject, sortBy } from 'ramda';

import { makeStyles, Typography, Theme } from '@material-ui/core';

import { useRequest, getData } from '@centreon/ui';

import { timeFormat, dateTimeFormat } from '../format';
import { parseAndFormat } from '../../dateTime';
import getTimeSeries, { getLineData } from './timeSeries';
import { GraphData, TimeValue, Line as LineModel } from './models';
import { labelNoDataForThisPeriod } from '../../translatedLabels';
import LoadingSkeleton from './LoadingSkeleton';
import Legend from './Legend';
import getGraphLines from './Lines';

interface Props {
  endpoint: string;
  xAxisTickFormat?: string;
  graphHeight: number;
  toggableLegend?: boolean;
}

const useStyles = makeStyles<Theme, Pick<Props, 'graphHeight'>>((theme) => ({
  container: {
    display: 'grid',
    flexDirection: 'column',
    gridTemplateRows: ({ graphHeight }): string => `auto ${graphHeight}px auto`,
    gridGap: theme.spacing(1),
    height: '100%',
    justifyItems: 'center',
  },
  noDataContainer: {
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    height: '100%',
  },
  graph: {
    width: '100%',
    height: '100%',
  },
  legend: {
    display: 'flex',
    flexWrap: 'wrap',
    justifyContent: 'center',
    alignItems: 'center',
    width: '100%',
  },
}));

const PerformanceGraph = ({
  endpoint,
  graphHeight,
  xAxisTickFormat = timeFormat,
  toggableLegend = false,
}: Props): JSX.Element | null => {
  const classes = useStyles({ graphHeight });

  const [timeSeries, setTimeSeries] = React.useState<Array<TimeValue>>();
  const [lineData, setLineData] = React.useState<Array<LineModel>>();
  const [title, setTitle] = React.useState<string>();

  const { sendRequest, sending } = useRequest<GraphData>({
    request: getData,
  });

  React.useEffect(() => {
    sendRequest(endpoint).then((graphData) => {
      setTimeSeries(getTimeSeries(graphData));
      setLineData(getLineData(graphData));
      setTitle(graphData.global.title);
    });
  }, [endpoint]);

  if (sending) {
    return <LoadingSkeleton />;
  }

  if (!timeSeries || !lineData) {
    return (
      <div className={classes.noDataContainer}>
        <Typography align="center" variant="body1">
          {labelNoDataForThisPeriod}
        </Typography>
      </div>
    );
  }

  const sortedLines = sortBy(prop('name'), lineData);
  const displayedLines = reject(propEq('display', false), sortedLines);

  const formatValue = ({ value, unit }): string => {
    const base2Units = [
      'B',
      'bytes',
      'bytespersecond',
      'B/s',
      'B/sec',
      'o',
      'octets',
    ];
    const base = base2Units.includes(unit) ? 2 : 10;

    return filesize(value, { base }).replace('B', '');
  };

  const formatTooltipValue = (value, metric, { unit }): Array<string> => {
    const legendName = pipe(
      find(propEq('metric', metric)),
      path(['name']),
    )(lineData) as string;

    return [formatValue({ value, unit }), legendName];
  };

  const formatXAxisTick = (tick): string =>
    parseAndFormat({ isoDate: tick, to: xAxisTickFormat });

  const formatTooltipTime = (tick): string =>
    parseAndFormat({ isoDate: tick, to: dateTimeFormat });

  const getLineByMetric = (metric): LineModel => {
    return find(propEq('metric', metric), lineData) as LineModel;
  };

  const toggleMetricDisplay = (metric): void => {
    const line = getLineByMetric(metric);

    setLineData([
      ...reject(propEq('metric', metric), lineData),
      { ...line, display: !line.display },
    ]);
  };

  const highlightLine = (metric): void => {
    const fadedLines = map((line) => ({ ...line, highlight: false }), lineData);

    setLineData([
      ...reject(propEq('metric', metric), fadedLines),
      { ...getLineByMetric(metric), highlight: true },
    ]);
  };

  const clearHighlight = (): void => {
    setLineData(map((line) => ({ ...line, highlight: undefined }), lineData));
  };

  return (
    <div className={classes.container}>
      <Typography variant="body1" color="textPrimary">
        {title}
      </Typography>
      <ResponsiveContainer className={classes.graph}>
        <ComposedChart data={timeSeries} stackOffset="sign">
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis
            dataKey="time"
            tickFormatter={formatXAxisTick}
            tick={{ fontSize: 13 }}
          />

          {getGraphLines({ lines: displayedLines, formatValue })}

          <Tooltip
            labelFormatter={formatTooltipTime}
            formatter={formatTooltipValue}
          />
        </ComposedChart>
      </ResponsiveContainer>
      <div className={classes.legend}>
        <Legend
          lines={sortedLines}
          onItemToggle={toggleMetricDisplay}
          toggable={toggableLegend}
          onItemHighlight={highlightLine}
          onClearItemHighlight={clearHighlight}
        />
      </div>
    </div>
  );
};

export default PerformanceGraph;
