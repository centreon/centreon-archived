import * as React from 'react';

import { XAxis, ResponsiveContainer, AreaChart, Area } from 'recharts';

import { useTheme } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { getStatusColors, useRequest, getData } from '@centreon/ui';

import getTimeSeries from './timeSeries';
import { timeFormat } from '../format';
import { parseAndFormat } from '../../dateTime';
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

  const { sendRequest } = useRequest<GraphData>({
    request: getData,
  });

  React.useEffect(() => {
    sendRequest(endpoint).then(setGraphData);
  }, [endpoint]);

  if (graphData === undefined) {
    return <LoadingSkeleton />;
  }

  const timeSeries = getTimeSeries(graphData);

  const formatToxAxisTickFormat = (tick): string =>
    parseAndFormat({ isoDate: tick, to: xAxisTickFormat });

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

        <XAxis dataKey="time" tickFormatter={formatToxAxisTickFormat} />
        <Area dataKey="value" stroke="transparent" fill="url(#splitColor)" />
      </AreaChart>
    </ResponsiveContainer>
  );
};

export default StatusGraph;
