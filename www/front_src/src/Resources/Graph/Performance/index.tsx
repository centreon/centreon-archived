import * as React from 'react';

import {
  ComposedChart,
  Area,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
} from 'recharts';
import filesize from 'filesize';
import {
  pipe,
  map,
  uniq,
  prop,
  propEq,
  find,
  path,
  reject,
  sortBy,
} from 'ramda';

import {
  fade,
  makeStyles,
  Typography,
  Theme,
  FormControlLabel,
  Checkbox,
} from '@material-ui/core';

import { useRequest, getData } from '@centreon/ui';

import { timeFormat, dateTimeFormat } from '../format';
import { parseAndFormat } from '../../dateTime';
import getTimeSeries, { getLines } from './timeSeries';
import { GraphData, TimeValue, Line as LineModel } from './models';
import { labelNoDataForThisPeriod } from '../../translatedLabels';
import LoadingSkeleton from './LoadingSkeleton';
import identity from '../../identity';
import Legend from './Legend';

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
    sendRequest('http://localhost:5000/mock/graph').then((graphData) => {
      setTimeSeries(getTimeSeries(graphData));
      setLineData(getLines(graphData));
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

  const getBase = (unit): 2 | 10 => {
    const base2Units = [
      'B',
      'bytes',
      'bytespersecond',
      'B/s',
      'B/sec',
      'o',
      'octets',
    ];

    return base2Units.includes(unit) ? 2 : 10;
  };

  const sortedLines = sortBy(prop('name'), lineData);
  const displayedLines = reject(propEq('display', false), sortedLines);

  const getUnits = (): Array<string> => {
    return pipe(map(prop('unit')), uniq)(displayedLines);
  };

  const displayMultipleYAxes = getUnits().length < 3;

  const formatValue = ({ value, unit }): string => {
    return filesize(value, { base: getBase(unit) }).replace('B', '');
  };

  const YAxes = displayMultipleYAxes ? (
    getUnits().map((unit, index) => (
      <YAxis
        yAxisId={unit}
        key={unit}
        orientation={index === 0 ? 'left' : 'right'}
        tickFormatter={(tick): string => formatValue({ value: tick, unit })}
        tick={{ fontSize: 12 }}
      />
    ))
  ) : (
    <YAxis tick={{ fontSize: 12 }} />
  );

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

  const toggleMetricDisplay = ({ checked, metric }): void => {
    const line = find(propEq('metric', metric), lineData) as LineData;

    setLineData([
      ...reject(propEq('metric', metric), lineData),
      { ...line, display: checked },
    ]);
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
          {YAxes}
          {displayedLines.map(
            ({ metric, areaColor, transparency, lineColor, filled, unit }) => {
              const LineComponent = identity(filled ? Area : Line);

              return (
                <LineComponent
                  key={metric}
                  dot={false}
                  dataKey={metric}
                  unit={unit}
                  stroke={lineColor}
                  yAxisId={displayMultipleYAxes ? unit : undefined}
                  isAnimationActive={false}
                  fill={
                    transparency
                      ? fade(areaColor, transparency * 0.01)
                      : undefined
                  }
                />
              );
            },
          )}

          <Tooltip
            labelFormatter={formatTooltipTime}
            formatter={formatTooltipValue}
          />
        </ComposedChart>
      </ResponsiveContainer>
      <div className={classes.legend}>
        <Legend lines={sortedLines} onItemToggle={toggleMetricDisplay} />
      </div>
    </div>
  );
};

export default PerformanceGraph;
