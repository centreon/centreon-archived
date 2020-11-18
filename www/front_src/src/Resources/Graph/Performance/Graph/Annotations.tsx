import * as React from 'react';

import { Line } from '@visx/visx';
import { propEq, filter } from 'ramda';
import { ScaleTime } from 'd3-scale';
import { useTranslation } from 'react-i18next';

import {
  useTheme,
  Tooltip,
  Typography,
  Paper,
  makeStyles,
} from '@material-ui/core';
import IconComment from '@material-ui/icons/Comment';

import { TimelineEvent } from '../../../Details/tabs/Timeline/models';
import { labelBy } from '../../../translatedLabels';
import truncate from '../../../truncate';

const useStyles = makeStyles((theme) => ({
  tooltip: {
    backgroundColor: 'transparent',
  },
  tooltipContent: {
    padding: theme.spacing(1),
  },
}));

interface Props {
  xScale: ScaleTime<number, number>;
  timeline: Array<TimelineEvent>;
  graphHeight: number;
}

const Annotations = ({ xScale, timeline, graphHeight }: Props): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();
  const classes = useStyles();

  const comments = filter(propEq('type', 'comment'), timeline);

  return (
    <>
      {comments.map((comment) => {
        const iconHeight = 20;
        const yMargin = -30;
        const xMargin = -15;

        const x = xScale(new Date(comment.date));

        return (
          <g key={comment.id}>
            <Tooltip
              classes={{ tooltip: classes.tooltip }}
              title={
                <Paper className={classes.tooltipContent}>
                  <Typography variant="caption">
                    {`${truncate(comment.content)} ${t(labelBy)} ${
                      comment.contact?.name
                    }`}
                  </Typography>
                </Paper>
              }
            >
              <svg
                y={yMargin}
                x={x + xMargin}
                height={iconHeight}
                width={iconHeight}
              >
                <rect
                  width={iconHeight}
                  height={iconHeight}
                  fill="transparent"
                />
                <IconComment
                  height={iconHeight}
                  width={iconHeight}
                  color="primary"
                />
              </svg>
            </Tooltip>
            <Line
              from={{ x, y: yMargin + iconHeight + 2 }}
              to={{ x, y: graphHeight }}
              stroke={theme.palette.primary.main}
              strokeWidth={1}
              strokeOpacity={0.5}
              pointerEvents="none"
            />
          </g>
        );
      })}
    </>
  );
};

export default Annotations;
