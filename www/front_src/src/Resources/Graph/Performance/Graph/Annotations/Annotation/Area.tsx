import * as React from 'react';

import { Bar } from '@visx/visx';
import { ScaleTime } from 'd3-scale';
import { always, cond, equals, isNil, max, prop, T } from 'ramda';

import { fade, makeStyles } from '@material-ui/core';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import { TimelineEvent } from '../../../../../Details/tabs/Timeline/models';
import { labelFrom, labelTo } from '../../../../../translatedLabels';
import { Annotations, AnnotationsContext } from '..';

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
  startDate: string;
  endDate: string;
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

const AreaAnnotation = ({
  Icon,
  ariaLabel,
  color,
  graphHeight,
  xScale,
  startDate,
  endDate,
  ...props
}: Props): JSX.Element => {
  const { toDateTime } = useLocaleDateTimeFormat();

  const classes = useStyles();

  const { annotationHovered, setAnnotationHovered } = React.useContext(
    AnnotationsContext,
  ) as Annotations;

  const xIconMargin = -iconSize / 2;

  const xStart = max(xScale(new Date(startDate)), 0);
  const xEnd = endDate ? xScale(new Date(endDate)) : xScale.range()[1];

  const getFill = () =>
    cond<TimelineEvent | null, string>([
      [isNil, always(fade(color, 0.3))],
      [equals<TimelineEvent | null>(props.event), always(fade(color, 0.5))],
      [T, always(fade(color, 0.1))],
    ])(annotationHovered);

  const area = (
    <Bar
      x={xStart}
      y={yMargin + iconSize + 2}
      width={xEnd - xStart}
      height={graphHeight + iconSize / 2}
      fill={getFill()}
      onMouseEnter={() => setAnnotationHovered(() => props.event)}
      onMouseLeave={() => setAnnotationHovered(() => null)}
    />
  );

  const from = `${labelFrom} ${toDateTime(startDate)}`;
  const to = endDate ? ` ${labelTo} ${toDateTime(endDate)}` : '';

  const header = `${from}${to}`;

  const icon = (
    <Icon
      aria-label={ariaLabel}
      height={iconSize}
      width={iconSize}
      className={classes.icon}
      style={{
        color: getIconColor({
          annotationHovered,
          color,
          event: prop('event', props),
        }),
      }}
    />
  );

  return (
    <Annotation
      xIcon={xStart + (xEnd - xStart) / 2 + xIconMargin}
      marker={area}
      header={header}
      icon={icon}
      setAnnotationHovered={setAnnotationHovered}
      {...props}
    />
  );
};

export default AreaAnnotation;
