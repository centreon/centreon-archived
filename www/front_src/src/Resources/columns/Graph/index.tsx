import * as React from 'react';

import {
  ComposedChart,
  Area,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
} from 'recharts';
import filesize from 'filesize';

import { BarChart as IconBarChart } from '@material-ui/icons';

import { useCancelTokenSource } from '@centreon/ui';

import { fade } from '@material-ui/core';
import { labelGraph } from '../../translatedLabels';
import HoverChip from '../HoverChip';
import { getData } from '../../api';

const Graph = ({ endpoint }) => {
  const { cancel, token } = useCancelTokenSource();
  const [graphData, setGraphData] = React.useState();

  React.useEffect(() => {
    getData<Array<unknown>>({
      endpoint,
      requestParams: { cancelToken: token },
    }).then((retrievedGraphData) => setGraphData(retrievedGraphData[0]));
    return (): void => cancel();
  }, []);

  const getMetricDataForTimeIndex = (timeIndex) => {
    return graphData.metrics.reduce(
      (metricByName, { metric, data }) => ({
        ...metricByName,
        [metric]: data[timeIndex],
      }),
      {},
    );
  };

  const getBase = (unit) => {
    const base1024 = [
      'B',
      'bytes',
      'bytespersecond',
      'B/s',
      'B/sec',
      'o',
      'octets',
    ];
    if (base1024.includes(unit)) {
      return 2;
    }

    return 10;
  };

  const data = graphData
    ? graphData.times.map((time, timeIndex) => {
        return { time, ...getMetricDataForTimeIndex(timeIndex) };
      })
    : [];

  const getUnits = () =>
    graphData ? [...new Set(graphData.metrics.map(({ unit }) => unit))] : [];

  return (
    <ComposedChart
      width={500}
      height={400}
      data={data}
      margin={{
        top: 10,
        right: 30,
        left: 0,
        bottom: 0,
      }}
    >
      <CartesianGrid strokeDasharray="3 3" />
      <XAxis dataKey="time" />
      {/* <YAxis /> */}
      {getUnits().map((unit, index) => (
        <YAxis
          yAxisId={unit === '' ? 'n/a' : unit}
          key={unit}
          orientation={index === 0 ? 'left' : 'right'}
          tickFormatter={(tick) => {
            console.log(tick);
            return unit === '' ? tick : filesize(tick, { base: getBase(unit) });
          }}
        />
      ))}

      {graphData?.metrics.map(({ metric, ds_data, unit }, index) =>
        ds_data.ds_filled ? (
          <Area
            type="monotone"
            dataKey={metric}
            stackId={index}
            stroke={ds_data.ds_color_line}
            fill={fade(ds_data.ds_color_area, 0.8)}
            yAxisId={unit === '' ? 'n/a' : unit}
          />
        ) : (
          <Line
            type="monotone"
            dataKey={metric}
            stroke={ds_data.ds_color_line}
            dot={false}
            yAxisId={unit === '' ? 'n/a' : unit}
          />
        ),
      )}
      <Legend />
    </ComposedChart>
  );
};

const GraphColumn = ({ Cell, row }): JSX.Element => {
  return (
    <Cell>
      <HoverChip
        ariaLabel={labelGraph}
        Icon={(): JSX.Element => <IconBarChart />}
      >
        <Graph endpoint="http://localhost:5000/api/beta/graph" />
      </HoverChip>
    </Cell>
  );
};

export default GraphColumn;
