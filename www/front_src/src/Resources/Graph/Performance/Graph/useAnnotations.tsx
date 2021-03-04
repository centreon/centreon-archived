import * as React from 'react';

import { always, cond, equals, isNil, not, pipe, T } from 'ramda';

import { fade } from '@material-ui/core';

import { TimelineEvent } from '../../../Details/tabs/Timeline/models';

export interface Annotations {
  annotationHovered: TimelineEvent | null;
  setAnnotationHovered: React.Dispatch<
    React.SetStateAction<TimelineEvent | null>
  >;
  getStrokeWidth: (event: TimelineEvent) => number;
  getStrokeOpacity: (event: TimelineEvent) => number;
  getFill: (props: GetColor) => string;
  getIconColor: (props: GetColor) => string;
}

interface GetColor {
  event: TimelineEvent;
  color: string;
}

export const useAnnotations = (): Annotations => {
  const [
    annotationHovered,
    setAnnotationHovered,
  ] = React.useState<TimelineEvent | null>(null);

  const getStrokeWidth = (event: TimelineEvent): number =>
    cond<TimelineEvent | null, number>([
      [isNil, always(1)],
      [equals<TimelineEvent | null>(event), always(3)],
      [T, always(1)],
    ])(annotationHovered);

  const getStrokeOpacity = (event: TimelineEvent): number =>
    cond<TimelineEvent | null, number>([
      [isNil, always(0.5)],
      [equals<TimelineEvent | null>(event), always(0.7)],
      [T, always(0.2)],
    ])(annotationHovered);

  const getFill = ({ color, event }: GetColor): string =>
    cond<TimelineEvent | null, string>([
      [isNil, always(fade(color, 0.3))],
      [equals<TimelineEvent | null>(event), always(fade(color, 0.5))],
      [T, always(fade(color, 0.1))],
    ])(annotationHovered);

  const getIconColor = ({ color, event }: GetColor): string =>
    cond<TimelineEvent | null, string>([
      [isNil, always(color)],
      [
        pipe(equals<TimelineEvent | null>(event), not),
        always(fade(color, 0.2)),
      ],
      [T, always(color)],
    ])(annotationHovered);

  return {
    annotationHovered,
    setAnnotationHovered,
    getStrokeWidth,
    getStrokeOpacity,
    getFill,
    getIconColor,
  };
};

export default useAnnotations;
