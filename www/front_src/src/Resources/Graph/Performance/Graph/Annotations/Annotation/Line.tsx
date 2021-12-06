import * as React from 'react';

import { Shape } from '@visx/visx';
import { ScaleTime } from 'd3-scale';
import { prop } from 'ramda';

import { makeStyles } from '@material-ui/core';

import { useLocaleDateTimeFormat, useMemoComponent } from '@centreon/ui';

import useAnnotationsContext from '../../Context';

import Annotation, { Props as AnnotationProps, yMargin, iconSize } from '.';

type Props = {
  Icon: (props) => JSX.Element;
  ariaLabel: string;
  color: string;
  date: string;
  graphHeight: number;
  xScale: ScaleTime<number, number>;
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
    annotationHovered,
    setAnnotationHovered,
    getStrokeWidth,
    getStrokeOpacity,
    getIconColor,
  } = useAnnotationsContext();

  const xIconMargin = -iconSize / 2;

  const xIcon = xScale(new Date(date));

  const header = toDateTime(date);

  const line = (
    <Shape.Line
      from={{ x: xIcon, y: yMargin + iconSize + 2 }}
      stroke={color}
      strokeOpacity={getStrokeOpacity(prop('event', props))}
      strokeWidth={getStrokeWidth(prop('event', props))}
      to={{ x: xIcon, y: graphHeight }}
    />
  );

  const icon = (
    <Icon
      aria-label={ariaLabel}
      className={classes.icon}
      height={iconSize}
      style={{
        color: getIconColor({
          color,
          event: prop('event', props),
        }),
      }}
      width={iconSize}
    />
  );

  return useMemoComponent({
    Component: (
      <Annotation
        header={header}
        icon={icon}
        marker={line}
        setAnnotationHovered={setAnnotationHovered}
        xIcon={xIcon + xIconMargin}
        {...props}
      />
    ),
    memoProps: [annotationHovered, xIcon],
  });
};

export default LineAnnotation;
