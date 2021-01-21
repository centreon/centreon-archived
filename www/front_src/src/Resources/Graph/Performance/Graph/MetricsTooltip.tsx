import * as React from 'react';

import { take, takeLast } from 'ramda';
import clsx from 'clsx';

import { Typography, makeStyles } from '@material-ui/core';

import { useLocaleDateTimeFormat, dateTimeFormat } from '@centreon/ui';

import { getLineForMetric } from '../timeSeries';
import formatMetricValue from '../formatMetricValue';
import { Line, TimeValue } from '../models';
import LegendMarker from '../Legend/Marker';

interface Props {
  lines: Array<Line>;
  timeValue: TimeValue;
  base: number;
  metrics: Array<string>;
}

const useStyles = makeStyles((theme) => ({
  tooltip: {
    display: 'flex',
    flexDirection: 'column',
  },
  emphasized: {
    fontWeight: 'bold',
  },
  metric: {
    display: 'grid',
    gridTemplateColumns: 'auto 1fr auto',
    alignItems: 'center',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(0.5),
    justifyContent: 'flex-start',
  },
  value: {
    justifySelf: 'flex-end',
  },
}));

const truncateInMiddle = (label: string): string => {
  const maxLength = 50;

  if (label.length < maxLength) {
    return label;
  }

  return `${take(maxLength / 2, label)}...${takeLast(maxLength / 2, label)}`;
};

const MetricsTooltip = ({
  lines,
  timeValue,
  base,
  metrics,
}: Props): JSX.Element | null => {
  const classes = useStyles();
  const { format } = useLocaleDateTimeFormat();

  return (
    <div className={classes.tooltip}>
      <Typography variant="caption" className={classes.emphasized}>
        {format({
          date: new Date(timeValue.timeTick),
          formatString: dateTimeFormat,
        })}
      </Typography>
      {metrics.map((metric) => {
        const value = timeValue[metric] as number;

        const { color, name, unit } = getLineForMetric({
          lines,
          metric,
        }) as Line;

        const formattedValue = formatMetricValue({ value, unit, base });

        return (
          <div className={classes.metric} key={metric}>
            <LegendMarker color={color} />
            <Typography variant="caption" noWrap>
              {truncateInMiddle(name)}
            </Typography>
            <Typography
              variant="caption"
              className={clsx([classes.value, classes.emphasized])}
            >
              {formattedValue}
            </Typography>
          </div>
        );
      })}
    </div>
  );
};

export default MetricsTooltip;
