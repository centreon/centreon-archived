import * as React from 'react';

import clsx from 'clsx';
import { take, takeLast } from 'ramda';

import { Typography, makeStyles } from '@material-ui/core';

import LegendMarker, { LegendMarkerVariant } from '../Legend/Marker';

import { useMetricsValueContext } from './useMetricsValue';

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

const MetricsTooltip = (): JSX.Element | null => {
  const classes = useStyles();
  const {
    metricsValue,
    getFormattedMetricData,
    formatDate,
  } = useMetricsValueContext();

  return (
    <div className={classes.tooltip}>
      <Typography
        variant="caption"
        align="center"
        className={classes.emphasized}
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
            <Typography variant="caption" noWrap>
              {truncateInMiddle(data?.name || '')}
            </Typography>
            <Typography
              variant="caption"
              className={clsx([classes.value, classes.emphasized])}
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
