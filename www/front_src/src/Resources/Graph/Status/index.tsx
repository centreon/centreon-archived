import * as React from 'react';

import { XAxis, ResponsiveContainer, AreaChart, Area } from 'recharts';

import { useTheme } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { getStatusColors } from '@centreon/ui';

import useGet from '../../useGet';
import getTimeSeries from './timeSeries';
import { formatTimeAxis } from '../format';

interface GraphData {
  critical: Array<number>;
  warning: Array<number>;
  ok: Array<number>;
  unknown: Array<number>;
}

const LoadingSkeleton = (): JSX.Element => {
  return <Skeleton height="100%" />;
};

interface Props {
  endpoint: string;
}

const StatusGraph = ({ endpoint }: Props): JSX.Element | null => {
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

  const bars = getTimeSeries(graphData).map(({ time }) => (
    <Area
      key={time}
      dataKey="value"
      stroke="transparent"
      fill="url(#splitColor)"
    />
  ));

  return (
    <ResponsiveContainer>
      <AreaChart data={getTimeSeries(graphData)}>
        <defs>
          <linearGradient id="splitColor" x1="0" y1="0" x2="1" y2="0">
            {getTimeSeries(graphData).map(
              ({ fraction, severityCode, time }) => (
                <stop
                  key={time}
                  offset={fraction}
                  stopColor={
                    getStatusColors({ theme, severityCode }).backgroundColor
                  }
                  stopOpacity={1}
                />
              ),
            )}
          </linearGradient>
        </defs>

        <XAxis dataKey="time" tickFormatter={formatTimeAxis} />
        {bars}
      </AreaChart>
    </ResponsiveContainer>
  );
};

export default StatusGraph;
