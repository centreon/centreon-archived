import * as React from 'react';

import { Bar } from '@visx/visx';
import { ScaleTime } from 'd3-scale';
import { max, prop } from 'ramda';

import { makeStyles } from '@material-ui/core';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import { labelFrom, labelTo } from '../../../../../translatedLabels';
import useAnnotationsContext from '../../Context';

import Annotation, { Props as AnnotationProps, yMargin, iconSize } from '.';

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

  const {
    setAnnotationHovered,
    getFill,
    getIconColor,
  } = useAnnotationsContext();

  const xIconMargin = -iconSize / 2;

  const xStart = max(xScale(new Date(startDate)), 0);
  const xEnd = endDate ? xScale(new Date(endDate)) : xScale.range()[1];

  const area = (
    <Bar
      x={xStart}
      y={yMargin + iconSize + 2}
      width={xEnd - xStart}
      height={graphHeight + iconSize / 2}
      fill={getFill({ event: prop('event', props), color })}
      onMouseEnter={() => setAnnotationHovered(() => prop('event', props))}
      onMouseLeave={() => setAnnotationHovered(() => undefined)}
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
