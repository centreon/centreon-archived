import * as React from 'react';

import { Line } from '@visx/visx';
import { ScaleTime } from 'd3-scale';

import { makeStyles, Tooltip, Paper, Typography } from '@material-ui/core';

import { useLocaleDateTimeFormat } from '@centreon/ui';

const useStyles = makeStyles((theme) => ({
  tooltip: {
    backgroundColor: 'transparent',
  },
  tooltipContent: {
    padding: theme.spacing(1),
  },
}));

interface Props {
  icon: JSX.Element;
  color: string;
  date: string;
  content: string;
  xScale: ScaleTime<number, number>;
  graphHeight: number;
  iconSize: number;
}

const Annotation = ({
  icon,
  color,
  date,
  content,
  graphHeight,
  iconSize,
  xScale,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { toDateTime } = useLocaleDateTimeFormat();

  const yMargin = -30;
  const xMargin = -15;

  const x = xScale(new Date(date));

  return (
    <g>
      <Tooltip
        classes={{ tooltip: classes.tooltip }}
        title={
          <Paper className={classes.tooltipContent}>
            <Typography variant="body2">{toDateTime(date)}</Typography>
            <Typography variant="caption">{content}</Typography>
          </Paper>
        }
      >
        <svg y={yMargin} x={x + xMargin} height={iconSize} width={iconSize}>
          <rect width={iconSize} height={iconSize} fill="transparent" />
          {icon}
        </svg>
      </Tooltip>
      <Line
        from={{ x, y: yMargin + iconSize + 2 }}
        to={{ x, y: graphHeight }}
        stroke={color}
        strokeWidth={1}
        strokeOpacity={0.5}
        pointerEvents="none"
      />
    </g>
  );
};

export default Annotation;
