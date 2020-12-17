import * as React from 'react';

import { Line } from '@visx/visx';
import { ScaleTime } from 'd3-scale';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import Annotation, { Props as AnnotationProps, yMargin, iconSize } from '.';

type Props = {
  color: string;
  graphHeight: number;
  xScale: ScaleTime<number, number>;
  date: string;
} & Omit<AnnotationProps, 'marker' | 'xIcon' | 'header'>;

const LineAnnotation = ({
  color,
  graphHeight,
  xScale,
  date,
  ...props
}: Props): JSX.Element => {
  const { toDateTime } = useLocaleDateTimeFormat();

  const xIconMargin = -iconSize / 2;

  const xIcon = xScale(new Date(date));

  const header = toDateTime(date);

  const line = (
    <Line
      from={{ x: xIcon, y: yMargin + iconSize + 2 }}
      to={{ x: xIcon, y: graphHeight }}
      stroke={color}
      strokeWidth={1}
      strokeOpacity={0.5}
      pointerEvents="none"
    />
  );

  return (
    <Annotation
      xIcon={xIcon + xIconMargin}
      marker={line}
      header={header}
      {...props}
    />
  );
};

export default LineAnnotation;
