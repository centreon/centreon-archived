import * as React from 'react';

import { findLast, gt } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  alpha,
  LinearProgress,
  makeStyles,
  Theme,
  Typography,
} from '@material-ui/core';

import memoizeComponent from '../Resources/memoizedComponent';

interface Threshold {
  color: string;
  label: string;
  value: number;
}

interface Props {
  max: number;
  thresholds: Array<Threshold>;
  value: number;
}
const useStyles = makeStyles<Theme, Threshold>((theme) => ({
  label: {
    color: ({ color }): string => color,
  },
  linear: {
    backgroundColor: ({ color }): string => color,
  },
  linearBackground: {
    backgroundColor: ({ color }): string => alpha(color, 0.3),
    width: '100%',
  },
  progressContainer: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
    display: 'flex',
    width: '100%',
  },
}));

const StrengthProgress = ({ thresholds, max, value }: Props): JSX.Element => {
  const currentThreshold =
    findLast((threshold) => value >= threshold.value, thresholds) ||
    thresholds[0];
  const classes = useStyles(currentThreshold);
  const { t } = useTranslation();

  const { label } = currentThreshold;

  const progressValue = gt(value, max) ? 100 : (value / max) * 100;

  return (
    <div className={classes.progressContainer}>
      <LinearProgress
        classes={{
          bar: classes.linear,
          root: classes.linearBackground,
        }}
        value={progressValue}
        variant="determinate"
      />
      <Typography className={classes.label} variant="caption">
        {t(label)}
      </Typography>
    </div>
  );
};

export default memoizeComponent<Props>({
  Component: StrengthProgress,
  memoProps: ['thresholds', 'max', 'value'],
});
