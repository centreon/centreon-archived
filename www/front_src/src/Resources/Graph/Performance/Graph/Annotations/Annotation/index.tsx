import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { always, cond, equals, isNil, not, pipe, T } from 'ramda';

import {
  makeStyles,
  Tooltip,
  Paper,
  Typography,
  fade,
} from '@material-ui/core';

import truncate from '../../../../../truncate';
import { TimelineEvent } from '../../../../../Details/tabs/Timeline/models';
import { labelBy } from '../../../../../translatedLabels';

const yMargin = -32;
const iconSize = 20;

const useStyles = makeStyles((theme) => ({
  tooltip: {
    backgroundColor: 'transparent',
  },
  tooltipContent: {
    padding: theme.spacing(1),
  },
}));

export interface Props {
  xIcon: number;
  header: string;
  event: TimelineEvent;
  marker: JSX.Element;
  icon: JSX.Element;
  setAnnotationHovered: React.Dispatch<
    React.SetStateAction<TimelineEvent | null>
  >;
}

interface GetIconColor {
  annotationHovered: TimelineEvent | null;
  event: TimelineEvent;
  color: string;
}

export const getIconColor = ({
  annotationHovered,
  color,
  event,
}: GetIconColor): string =>
  cond<TimelineEvent | null, string>([
    [isNil, always(color)],
    [pipe(equals<TimelineEvent | null>(event), not), always(fade(color, 0.2))],
    [T, always(color)],
  ])(annotationHovered);

const Annotation = ({
  icon,
  header,
  event,
  xIcon,
  marker,
  setAnnotationHovered,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const content = `${truncate(event.content)} (${t(labelBy)} ${
    event.contact?.name
  })`;

  return (
    <g>
      <Tooltip
        classes={{ tooltip: classes.tooltip }}
        title={
          <Paper className={classes.tooltipContent}>
            <Typography variant="body2">{header}</Typography>
            <Typography variant="caption">{content}</Typography>
          </Paper>
        }
      >
        <svg
          y={yMargin}
          x={xIcon}
          height={iconSize}
          width={iconSize}
          onMouseEnter={() => setAnnotationHovered(() => event)}
          onMouseLeave={() => setAnnotationHovered(() => null)}
        >
          <rect width={iconSize} height={iconSize} fill="transparent" />
          {icon}
        </svg>
      </Tooltip>
      {marker}
    </g>
  );
};

export default Annotation;
export { yMargin, iconSize };
