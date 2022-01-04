import { ScaleTime } from 'd3-scale';
import { atom } from 'jotai';
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
  or,
  pipe,
  Pred,
  T,
  __,
} from 'ramda';
import { alpha } from '@mui/material';

import { TimelineEvent } from '../../../Details/tabs/Timeline/models';

interface ChangeAnnotationHoveredProps {
  graphWidth: number;
  mouseX: number;
  resourceId: string;
  timeline: Array<TimelineEvent> | undefined;
  xScale: ScaleTime<number, number>;
}

interface GetIsBetweenProps {
  end: number;
  start: number;
}

interface AnnotationColor {
  annotation: AnnotationAtom;
  color: string;
}

interface AnnotationAtom {
  event?: TimelineEvent;
  resourceId: string;
}

interface AnnotationWithHoveredState {
  annotation: AnnotationAtom | undefined;
  hoveredAnnotation: AnnotationAtom | undefined;
}

export const annotationHoveredAtom = atom<AnnotationAtom | undefined>(
  undefined,
);

export const getIsBetween = ({ start, end }: GetIsBetweenProps): Pred => {
  const gteX = gte(__, start);
  const lteX = lte(__, end);

  return both(gteX, lteX);
};

export const changeAnnotationHoveredDerivedAtom = atom(
  null,
  (
    _,
    set,
    {
      xScale,
      mouseX,
      timeline,
      graphWidth,
      resourceId,
    }: ChangeAnnotationHoveredProps,
  ) => {
    const isWithinErrorMargin = getIsBetween({
      end: inc(mouseX),
      start: dec(mouseX),
    });

    const annotationHovered = find(
      ({ startDate, endDate, date }: TimelineEvent) => {
        if (isNil(startDate)) {
          return isWithinErrorMargin(xScale(new Date(date)));
        }

        const isBetweenStartAndEndDate = getIsBetween({
          end: xScale(
            endDate ? new Date(endDate) : last(xScale.domain()) || graphWidth,
          ),
          start: xScale(new Date(startDate as string)),
        });

        return isBetweenStartAndEndDate(mouseX);
      },
      timeline ?? [],
    );

    set(annotationHoveredAtom, {
      event: annotationHovered,
      resourceId,
    });
  },
);

const getIsNotHoveredOrNil = ({
  hoveredAnnotation,
  annotation,
}: AnnotationWithHoveredState): boolean =>
  or(
    isNil(hoveredAnnotation?.event),
    not(equals(hoveredAnnotation?.resourceId, annotation?.resourceId)),
  );

export const getStrokeWidthDerivedAtom = atom(
  (get) =>
    (annotation: AnnotationAtom | undefined): number =>
      cond<AnnotationAtom | undefined, number>([
        [
          (hoveredAnnotation): boolean =>
            getIsNotHoveredOrNil({ annotation, hoveredAnnotation }),
          always(1),
        ],
        [equals(annotation), always(3)],
        [T, always(1)],
      ])(get(annotationHoveredAtom)),
);

export const getStrokeOpacityDerivedAtom = atom(
  (get) =>
    (annotation: AnnotationAtom | undefined): number =>
      cond<AnnotationAtom | undefined, number>([
        [
          (hoveredAnnotation): boolean =>
            getIsNotHoveredOrNil({ annotation, hoveredAnnotation }),
          always(0.5),
        ],
        [equals(annotation), always(0.7)],
        [T, always(0.2)],
      ])(get(annotationHoveredAtom)),
);

export const getFillColorDerivedAtom = atom(
  (get) =>
    ({ color, annotation }: AnnotationColor): string =>
      cond<AnnotationAtom | undefined, string>([
        [
          (hoveredAnnotation): boolean =>
            getIsNotHoveredOrNil({ annotation, hoveredAnnotation }),
          always(alpha(color, 0.3)),
        ],
        [
          equals<AnnotationAtom | undefined>(annotation),
          always(alpha(color, 0.5)),
        ],
        [T, always(alpha(color, 0.1))],
      ])(get(annotationHoveredAtom)),
);

export const getIconColorDerivedAtom = atom(
  (get) =>
    ({ color, annotation }: AnnotationColor): string =>
      cond<AnnotationAtom | undefined, string>([
        [
          (hoveredAnnotation): boolean =>
            getIsNotHoveredOrNil({ annotation, hoveredAnnotation }),
          always(color),
        ],
        [
          pipe(equals<AnnotationAtom | undefined>(annotation), not),
          always(alpha(color, 0.2)),
        ],
        [T, always(color)],
      ])(get(annotationHoveredAtom)),
);
