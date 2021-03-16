import * as React from 'react';

import { filter, propEq, isNil } from 'ramda';
import { ScaleTime } from 'd3-scale';

import { TimelineEvent } from '../../../../Details/tabs/Timeline/models';

import LineAnnotation from './Annotation/Line';
import AreaAnnotation from './Annotation/Area';

interface Props {
  type: string;
  xScale: ScaleTime<number, number>;
  timeline: Array<TimelineEvent>;
  graphHeight: number;
  Icon: (props) => JSX.Element;
  ariaLabel: string;
  color: string;
}

const EventAnnotations = ({
  type,
  xScale,
  timeline,
  graphHeight,
  Icon,
  ariaLabel,
  color,
}: Props): JSX.Element => {
  const events = filter<TimelineEvent>(propEq('type', type), timeline);

  return (
    <>
      {events.map((event) => {
        const props = {
          Icon,
          ariaLabel,
          graphHeight,
          color,
          xScale,
          event,
        };

        if (isNil(event.startDate) && isNil(event.endDate)) {
          return <LineAnnotation key={event.id} date={event.date} {...props} />;
        }

        return (
          <AreaAnnotation
            key={event.id}
            startDate={event.startDate as string}
            endDate={event.endDate as string}
            {...props}
          />
        );
      })}
    </>
  );
};

export default EventAnnotations;
