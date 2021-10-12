import * as React from 'react';

import {
  ComposedChart,
  XAxis,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
} from 'recharts';
import {
  pipe,
  map,
  prop,
  propEq,
  find,
  path,
  reject,
  sortBy,
  isEmpty,
  isNil,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles, Typography, Theme } from '@material-ui/core';

import { useRequest, getData } from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import { timeFormat, dateTimeFormat } from '../format';
import { parseAndFormat } from '../../dateTime';
import { labelNoDataForThisPeriod } from '../../translatedLabels';

import getTimeSeries, { getLineData } from './timeSeries';
import { GraphData, TimeValue, Line as LineModel } from './models';
import LoadingSkeleton from './LoadingSkeleton';
import Legend from './Legend';
import getGraphLines from './Lines';
import formatMetricValue from './formatMetricValue';

const fontFamily = 'Roboto, sans-serif';

interface Props {
  endpoint?: string;
  graphHeight: number;
  toggableLegend?: boolean;
  xAxisTickFormat?: string;
}

const useStyles = makeStyles<Theme, Pick<Props, 'graphHeight'>>((theme) => ({
  container: {
    display: 'grid',
    flexDirection: 'column',
    gridGap: theme.spacing(1),
    gridTemplateRows: ({ graphHeight }): string => `auto ${graphHeight}px auto`,
    height: '100%',
    justifyItems: 'center',
  },
  graph: {
    height: '100%',
    width: '100%',
  },
  legend: {
    alignItems: 'center',
    display: 'flex',
    flexWrap: 'wrap',
    justifyContent: 'center',
    width: '100%',
  },
  noDataContainer: {
    alignItems: 'center',
    display: 'flex',
    height: '100%',
    justifyContent: 'center',
  },
}));

const PerformanceGraph = ({
  endpoint,
  graphHeight,
  xAxisTickFormat = timeFormat,
  toggableLegend = false,
}: Props): JSX.Element | null => {
  const classes = useStyles({ graphHeight });
  const { t } = useTranslation();
  const { locale } = useUserContext();

  const [timeSeries, setTimeSeries] = React.useState<Array<TimeValue>>([]);
  const [lineData, setLineData] = React.useState<Array<LineModel>>([]);
  const [title, setTitle] = React.useState<string>();
  const [base, setBase] = React.useState<number>();

  const { sendRequest, sending } = useRequest<GraphData>({
    request: getData,
  });

  React.useEffect(() => {
    if (isNil(endpoint)) {
      return;
    }

    sendRequest(endpoint).then((graphData) => {
      setTimeSeries(getTimeSeries(graphData));
      setLineData(getLineData(graphData));
      setTitle(graphData.global.title);
      setBase(graphData.global.base);
    });
  }, [endpoint]);

  if (sending || isNil(endpoint)) {
    return <LoadingSkeleton />;
  }

  if (isEmpty(timeSeries) || isEmpty(lineData)) {
    return (
      <div className={classes.noDataContainer}>
        <Typography align="center" variant="body1">
          {t(labelNoDataForThisPeriod)}
        </Typography>
      </div>
    );
  }

  const sortedLines = sortBy(prop('name'), lineData);
  const displayedLines = reject(propEq('display', false), sortedLines);

  const formatTooltipValue = (
    value,
    metric,
    { unit },
  ): Array<string | null> => {
    const legendName = pipe(
      find(propEq('metric', metric)),
      path(['name']),
    )(lineData) as string;

    return [formatMetricValue({ base, unit, value }), legendName];
  };

  const formatXAxisTick = (tick): string =>
    parseAndFormat({ isoDate: tick, locale, to: xAxisTickFormat });

  const formatTooltipTime = (tick): string =>
    parseAndFormat({ isoDate: tick, locale, to: dateTimeFormat });

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
      <Typography color="textPrimary" variant="body1">
        {title}
      </Typography>
      <ResponsiveContainer className={classes.graph}>
        <ComposedChart data={timeSeries} stackOffset="sign">
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis
            dataKey="timeTick"
            tick={{ fontSize: 13 }}
            tickFormatter={formatXAxisTick}
          />

          {getGraphLines({ base, lines: displayedLines })}

          <Tooltip
            filterNull
            contentStyle={{ fontFamily }}
            formatter={formatTooltipValue}
            isAnimationActive={false}
            labelFormatter={formatTooltipTime}
            wrapperStyle={{ opacity: 0.7 }}
          />
        </ComposedChart>
      </ResponsiveContainer>
      <div className={classes.legend}>
        <Legend
          lines={sortedLines}
          toggable={toggableLegend}
          onClearItemHighlight={clearHighlight}
          onItemHighlight={highlightLine}
          onItemToggle={toggleMetricDisplay}
        />
      </div>
    </div>
  );
};

export default PerformanceGraph;
export { fontFamily };
