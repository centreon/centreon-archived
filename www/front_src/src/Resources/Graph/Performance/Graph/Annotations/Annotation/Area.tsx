import * as React from 'react';

import { Shape } from '@visx/visx';
import { ScaleTime } from 'd3-scale';
import { max, prop } from 'ramda';

import makeStyles from '@mui/styles/makeStyles';

import { useLocaleDateTimeFormat, useMemoComponent } from '@centreon/ui';

import { labelFrom, labelTo } from '../../../../../translatedLabels';
import useAnnotationsContext from '../../Context';

import Annotation, { Props as AnnotationProps, yMargin, iconSize } from '.';

type Props = {
  Icon: (props) => JSX.Element;
  ariaLabel: string;
  color: string;
  endDate: string;
  graphHeight: number;
  startDate: string;
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

  const { annotationHovered, setAnnotationHovered, getFill, getIconColor } =
    useAnnotationsContext();

  const xIconMargin = -iconSize / 2;

  const xStart = max(xScale(new Date(startDate)), 0);
  const xEnd = endDate ? xScale(new Date(endDate)) : xScale.range()[1];

  const area = (
    <Shape.Bar
      fill={getFill({ color, event: prop('event', props) })}
      height={graphHeight + iconSize / 2}
      width={xEnd - xStart}
      x={xStart}
      y={yMargin + iconSize + 2}
      onMouseEnter={(): void =>
        setAnnotationHovered(() => prop('event', props))
      }
      onMouseLeave={(): void => setAnnotationHovered(() => undefined)}
    />
  );

  const from = `${labelFrom} ${toDateTime(startDate)}`;
  const to = endDate ? ` ${labelTo} ${toDateTime(endDate)}` : '';

  const header = `${from}${to}`;

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
        marker={area}
        setAnnotationHovered={setAnnotationHovered}
        xIcon={xStart + (xEnd - xStart) / 2 + xIconMargin}
        {...props}
      />
    ),
    memoProps: [annotationHovered, xStart, xEnd],
  });
};

export default AreaAnnotation;
