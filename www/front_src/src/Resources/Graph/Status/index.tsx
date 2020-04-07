import * as React from 'react';

import { XAxis, ResponsiveContainer, AreaChart, Area } from 'recharts';

import { useTheme } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { getStatusColors } from '@centreon/ui';

import useGet from '../../useGet';
import getTimeSeries from './timeSeries';
import { formatTo, timeFormat } from '../format';
import { GraphData } from './models';

const LoadingSkeleton = (): JSX.Element => {
  return <Skeleton height="100%" />;
};

interface Props {
  endpoint: string;
  xAxisTickFormat: string;
}

const StatusGraph = ({
  endpoint,
  xAxisTickFormat = timeFormat,
}: Props): JSX.Element | null => {
  const theme = useTheme();

  const [graphData, setGraphData] = React.useState<GraphData>();

  const get = useGet({
    endpoint,
    onSuccess: setGraphData,
  });

  React.useEffect(() => {
    get();
  }, [endpoint]);

  if (graphData === undefined) {
    return <LoadingSkeleton />;
  }

  const timeSeries = getTimeSeries(graphData);

  const area = timeSeries.map(({ time }) => (
    <Area
      key={time}
      dataKey="value"
      stroke="transparent"
      fill="url(#splitColor)"
    />
  ));

  return (
    <ResponsiveContainer>
      <AreaChart data={timeSeries}>
        <defs>
          <linearGradient id="splitColor">
            {timeSeries.map(({ fraction, offset, severityCode, time }) => [
              <stop
                key={`start-${time}`}
                offset={offset}
                stopColor={
                  getStatusColors({ theme, severityCode }).backgroundColor
                }
              />,
              <stop
                key={`end-${time}`}
                offset={offset + fraction}
                stopColor={
                  getStatusColors({ theme, severityCode }).backgroundColor
                }
              />,
            ])}
          </linearGradient>
        </defs>

        <XAxis
          dataKey="time"
          tickFormatter={(tick): string =>
            formatTo({ time: tick, to: xAxisTickFormat })
          }
        />
        {area}
      </AreaChart>
    </ResponsiveContainer>
  );
};

export default StatusGraph;
