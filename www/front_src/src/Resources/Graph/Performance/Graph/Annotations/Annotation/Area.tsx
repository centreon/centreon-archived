import * as React from 'react';

import { Bar } from '@visx/visx';
import { ScaleTime } from 'd3-scale';
import { max } from 'ramda';

import { fade } from '@material-ui/core';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import { labelFrom, labelTo } from '../../../../../translatedLabels';

import Annotation, { Props as AnnotationProps, yMargin, iconSize } from '.';

type Props = {
  color: string;
  graphHeight: number;
  xScale: ScaleTime<number, number>;
  startDate: string;
  endDate: string;
} & Omit<AnnotationProps, 'marker' | 'xIcon' | 'header'>;

const AreaAnnotation = ({
  color,
  graphHeight,
  xScale,
  startDate,
  endDate,
  ...props
}: Props): JSX.Element => {
  const { toDateTime } = useLocaleDateTimeFormat();

  const xIconMargin = -iconSize / 2;

  const xStart = max(xScale(new Date(startDate)), 0);
  const xEnd = endDate ? xScale(new Date(endDate)) : xScale.range()[1];

  const area = (
    <Bar
      x={xStart}
      y={yMargin + iconSize + 2}
      width={xEnd - xStart}
      height={graphHeight + iconSize / 2}
      fill={fade(color, 0.3)}
    />
  );

  const from = `${labelFrom} ${toDateTime(startDate)}`;
  const to = endDate ? ` ${labelTo} ${toDateTime(endDate)}` : '';

  const header = `${from}${to}`;

  return (
    <Annotation
      xIcon={xStart + (xEnd - xStart) / 2 + xIconMargin}
      marker={area}
      header={header}
      {...props}
    />
  );
};

export default AreaAnnotation;
