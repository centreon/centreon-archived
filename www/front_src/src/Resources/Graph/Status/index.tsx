import * as React from 'react';

import {
  BarChart,
  Bar,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
} from 'recharts';
import useGet from '../../useGet';
import getTimeSeries from './timeSeries';

interface GraphData {
  critical: Array<number>;
  warning: Array<number>;
  ok: Array<number>;
  unknown: Array<number>;
}

const Bars = ({ graphData }): JSX.Element => {
  return getTimeSeries(graphData).map(({ interval, severity }) => <Bar />);
};

const StatusGraph = (): JSX.Element | null => {
  const endpoint = 'http://localhost:5000/api/beta/status';
  const [graphData, setGraphData] = React.useState<GraphData>();

  const get = useGet({
    endpoint,
    onSuccess: setGraphData,
  });

  React.useEffect(() => {
    get();
  }, []);

  if (graphData === undefined) {
    return null;
  }

  return (
    <BarChart>
      <CartesianGrid strokeDasharray="3 3" />
      <XAxis dataKey="time" />
    </BarChart>
  );
};
