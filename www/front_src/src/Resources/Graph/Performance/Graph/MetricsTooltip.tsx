import * as React from 'react';

import clsx from 'clsx';
import { take, takeLast } from 'ramda';

import { Typography, makeStyles } from '@material-ui/core';

import LegendMarker, { LegendMarkerVariant } from '../Legend/Marker';

import { useMetricsValueContext } from './useMetricsValue';

const useStyles = makeStyles((theme) => ({
  emphasized: {
    fontWeight: 'bold',
  },
  metric: {
    alignItems: 'center',
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(0.5),
    gridTemplateColumns: 'auto 1fr auto',
    justifyContent: 'flex-start',
  },
  tooltip: {
    display: 'flex',
    flexDirection: 'column',
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

const MetricsTooltip = (): JSX.Element | null => {
  const classes = useStyles();
  const { metricsValue, getFormattedMetricData, formatDate } =
    useMetricsValueContext();

  return (
    <div className={classes.tooltip}>
      <Typography
        align="center"
        className={classes.emphasized}
        variant="caption"
      >
        {formatDate()}
      </Typography>
      {metricsValue?.metrics.map((metric) => {
        const data = getFormattedMetricData(metric);

        return (
          <div className={classes.metric} key={metric}>
            <LegendMarker
              color={data?.color || ''}
              variant={LegendMarkerVariant.dot}
            />
            <Typography noWrap variant="caption">
              {truncateInMiddle(data?.name || '')}
            </Typography>
            <Typography
              className={clsx([classes.value, classes.emphasized])}
              variant="caption"
            >
              {data?.formattedValue}
            </Typography>
          </div>
        );
      })}
    </div>
  );
};

export default MetricsTooltip;
