import * as React from 'react';

import { Typography } from '@material-ui/core';

import { useLocaleDateTimeFormat, dateTimeFormat } from '@centreon/ui';

import { getLineForMetric } from '../timeSeries';
import formatMetricValue from '../formatMetricValue';
import { Line, TimeValue } from '../models';

interface Props {
  lines: Array<Line>;
  timeValue: TimeValue;
  base: number;
  metrics: Array<string>;
}

const MetricsTooltip = ({
  lines,
  timeValue,
  base,
  metrics,
}: Props): JSX.Element | null => {
  const { format } = useLocaleDateTimeFormat();

  return (
    <div
      style={{
        display: 'flex',
        flexDirection: 'column',
      }}
    >
      <Typography variant="caption">
        {format({
          date: new Date(timeValue.timeTick),
          formatString: dateTimeFormat,
        })}
      </Typography>
      {metrics.map((metric) => {
        const value = timeValue[metric];

        const { color, name, unit } = getLineForMetric({
          lines,
          metric,
        }) as Line;

        const formattedValue = formatMetricValue({ value, unit, base });

        return (
          <Typography
            key={metric}
            variant="caption"
            style={{
              color,
            }}
          >
            {`${name} ${formattedValue}`}
          </Typography>
        );
      })}
    </div>
  );
};

export default MetricsTooltip;
