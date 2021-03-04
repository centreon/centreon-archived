import * as React from 'react';

import { Line } from '@visx/visx';
import { ScaleTime } from 'd3-scale';
import { always, cond, equals, isNil, prop, T } from 'ramda';

import { makeStyles } from '@material-ui/core';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import { TimelineEvent } from '../../../../../Details/tabs/Timeline/models';
import useAnnotationsContext from '../../Context';

import Annotation, {
  Props as AnnotationProps,
  yMargin,
  iconSize,
  getIconColor,
} from '.';

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

  const { annotationHovered, setAnnotationHovered } = useAnnotationsContext();

  const xIconMargin = -iconSize / 2;

  const xIcon = xScale(new Date(date));

  const header = toDateTime(date);

  const getStrokeWidth = () =>
    cond<TimelineEvent | null, number>([
      [isNil, always(1)],
      [equals<TimelineEvent | null>(props.event), always(3)],
      [T, always(1)],
    ])(annotationHovered);

  const getStrokeOpacity = () =>
    cond<TimelineEvent | null, number>([
      [isNil, always(0.5)],
      [equals<TimelineEvent | null>(props.event), always(0.7)],
      [T, always(0.2)],
    ])(annotationHovered);

  const line = (
    <Line
      from={{ x: xIcon, y: yMargin + iconSize + 2 }}
      to={{ x: xIcon, y: graphHeight }}
      stroke={color}
      strokeWidth={getStrokeWidth()}
      strokeOpacity={getStrokeOpacity()}
      onMouseEnter={() => setAnnotationHovered(() => props.event)}
      onMouseLeave={() => setAnnotationHovered(() => null)}
    />
  );

  const icon = (
    <Icon
      aria-label={ariaLabel}
      height={iconSize}
      width={iconSize}
      style={{
        color: getIconColor({
          annotationHovered,
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
