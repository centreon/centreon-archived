import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { makeStyles, Tooltip, Paper, Typography } from '@material-ui/core';

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
  event: TimelineEvent;
  header: string;
  icon: JSX.Element;
  marker: JSX.Element;
  setAnnotationHovered: React.Dispatch<
    React.SetStateAction<TimelineEvent | undefined>
  >;
  xIcon: number;
}

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
          height={iconSize}
          width={iconSize}
          x={xIcon}
          y={yMargin}
          onMouseEnter={(): void => setAnnotationHovered(() => event)}
          onMouseLeave={(): void => setAnnotationHovered(() => undefined)}
        >
          <rect fill="transparent" height={iconSize} width={iconSize} />
          {icon}
        </svg>
      </Tooltip>
      {marker}
    </g>
  );
};

export default Annotation;
export { yMargin, iconSize };
