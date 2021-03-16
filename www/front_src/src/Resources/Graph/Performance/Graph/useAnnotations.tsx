import * as React from 'react';

import {
  always,
  both,
  cond,
  dec,
  equals,
  find,
  gte,
  inc,
  isNil,
  last,
  lte,
  not,
  pipe,
  T,
  __,
} from 'ramda';
import { ScaleTime } from 'd3-scale';

import { fade } from '@material-ui/core';

import { TimelineEvent } from '../../../Details/tabs/Timeline/models';

export interface Annotations {
  annotationHovered: TimelineEvent | undefined;
  setAnnotationHovered: React.Dispatch<
    React.SetStateAction<TimelineEvent | undefined>
  >;
  getStrokeWidth: (event: TimelineEvent) => number;
  getStrokeOpacity: (event: TimelineEvent) => number;
  getFill: (props: GetColorProps) => string;
  getIconColor: (props: GetColorProps) => string;
  changeAnnotationHovered: (props: ChangeAnnotationHoveredProps) => void;
}

interface GetColorProps {
  event: TimelineEvent;
  color: string;
}

interface ChangeAnnotationHoveredProps {
  xScale: ScaleTime<number, number>;
  mouseX: number;
  timeline: Array<TimelineEvent> | undefined;
}

export const useAnnotations = (graphWidth: number): Annotations => {
  const [annotationHovered, setAnnotationHovered] = React.useState<
    TimelineEvent | undefined
  >(undefined);

  const getIsBetween = ({ xStart, xEnd }) => {
    const gteX = gte(__, xStart);
    const lteX = lte(__, xEnd);

    return both(gteX, lteX);
  };

  const changeAnnotationHovered = ({
    xScale,
    mouseX,
    timeline,
  }: ChangeAnnotationHoveredProps): void => {
    const isWithinErrorMargin = getIsBetween({
      xStart: dec(mouseX),
      xEnd: inc(mouseX),
    });

    setAnnotationHovered(
      find(({ startDate, endDate, date }: TimelineEvent) => {
        if (isNil(startDate)) {
          return isWithinErrorMargin(xScale(new Date(date)));
        }

        const isBetweenStartAndEndDate = getIsBetween({
          xStart: xScale(new Date(startDate as string)),
          xEnd: xScale(
            endDate ? new Date(endDate) : last(xScale.domain()) || graphWidth,
          ),
        });

        return isBetweenStartAndEndDate(mouseX);
      }, timeline ?? []),
    );
  };

  const getStrokeWidth = (event: TimelineEvent): number =>
    cond<TimelineEvent | undefined, number>([
      [isNil, always(1)],
      [equals<TimelineEvent | undefined>(event), always(3)],
      [T, always(1)],
    ])(annotationHovered);

  const getStrokeOpacity = (event: TimelineEvent): number =>
    cond<TimelineEvent | undefined, number>([
      [isNil, always(0.5)],
      [equals<TimelineEvent | undefined>(event), always(0.7)],
      [T, always(0.2)],
    ])(annotationHovered);

  const getFill = ({ color, event }: GetColorProps): string =>
    cond<TimelineEvent | undefined, string>([
      [isNil, always(fade(color, 0.3))],
      [equals<TimelineEvent | undefined>(event), always(fade(color, 0.5))],
      [T, always(fade(color, 0.1))],
    ])(annotationHovered);

  const getIconColor = ({ color, event }: GetColorProps): string =>
    cond<TimelineEvent | undefined, string>([
      [isNil, always(color)],
      [
        pipe(equals<TimelineEvent | undefined>(event), not),
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
    changeAnnotationHovered,
  };
};

export default useAnnotations;
