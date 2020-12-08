import * as React from 'react';

import { filter, propEq } from 'ramda';
import { ScaleTime } from 'd3-scale';

import { TimelineEvent } from '../../../../../Details/tabs/Timeline/models';

import Annotation from './Annotation';

interface Props {
  type: string;
  xScale: ScaleTime<number, number>;
  timeline: Array<TimelineEvent>;
  graphHeight: number;
  icon: JSX.Element;
  getContent: (event: TimelineEvent) => string;
  color: string;
  iconSize: number;
}

const EventAnnotations = ({
  type,
  xScale,
  timeline,
  graphHeight,
  icon,
  getContent,
  color,
  iconSize,
}: Props): JSX.Element => {
  const events = filter<TimelineEvent>(propEq('type', type), timeline);

  return (
    <>
      {events.map((event) => {
        const content = getContent(event);

        return (
          <Annotation
            key={event.id}
            icon={icon}
            content={content}
            date={event.date}
            graphHeight={graphHeight}
            iconSize={iconSize}
            color={color}
            xScale={xScale}
          />
        );
      })}
    </>
  );
};

export default EventAnnotations;
