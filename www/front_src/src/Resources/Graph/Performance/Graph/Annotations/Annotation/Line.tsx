import * as React from 'react';

import { Line } from '@visx/visx';
import { ScaleTime } from 'd3-scale';
import { prop } from 'ramda';

import { makeStyles } from '@material-ui/core';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import useAnnotationsContext from '../../Context';

import Annotation, { Props as AnnotationProps, yMargin, iconSize } from '.';

type Props = {
  color: string;
  graphHeight: number;
  xScale: ScaleTime<number, number>;
  date: string;
  Icon: (props) => JSX.Element;
  ariaLabel: string;
} & Omit<
  AnnotationProps,
  'marker' | 'xIcon' | 'header' | 'icon' | 'setAnnotationHovered'
>;

const useStyles = makeStyles((theme) => ({
  icon: {
    transition: theme.transitions.create('color', {
      duration: theme.transitions.duration.shortest,
    }),
  },
}));

const LineAnnotation = ({
  color,
  graphHeight,
  xScale,
  date,
  Icon,
  ariaLabel,
  ...props
}: Props): JSX.Element => {
  const { toDateTime } = useLocaleDateTimeFormat();

  const classes = useStyles();

  const {
    setAnnotationHovered,
    getStrokeWidth,
    getStrokeOpacity,
    getIconColor,
  } = useAnnotationsContext();

  const xIconMargin = -iconSize / 2;

  const xIcon = xScale(new Date(date));

  const header = toDateTime(date);

  const line = (
    <Line
      from={{ x: xIcon, y: yMargin + iconSize + 2 }}
      to={{ x: xIcon, y: graphHeight }}
      stroke={color}
      strokeWidth={getStrokeWidth(prop('event', props))}
      strokeOpacity={getStrokeOpacity(prop('event', props))}
    />
  );

  const icon = (
    <Icon
      aria-label={ariaLabel}
      height={iconSize}
      width={iconSize}
      style={{
        color: getIconColor({
          color,
          event: prop('event', props),
        }),
      }}
      className={classes.icon}
    />
  );

  return (
    <Annotation
      xIcon={xIcon + xIconMargin}
      marker={line}
      header={header}
      icon={icon}
      setAnnotationHovered={setAnnotationHovered}
      {...props}
    />
  );
};

export default LineAnnotation;
