import * as React from 'react';

import { filter, propEq, isNil } from 'ramda';
import { ScaleTime } from 'd3-scale';

import { TimelineEvent } from '../../../../Details/tabs/Timeline/models';

import LineAnnotation from './Annotation/Line';
import AreaAnnotation from './Annotation/Area';

interface Props {
  Icon: (props) => JSX.Element;
  ariaLabel: string;
  color: string;
  graphHeight: number;
  timeline: Array<TimelineEvent>;
  type: string;
  xScale: ScaleTime<number, number>;
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
          color,
          event,
          graphHeight,
          xScale,
        };

        if (isNil(event.startDate) && isNil(event.endDate)) {
          return <LineAnnotation date={event.date} key={event.id} {...props} />;
        }

        return (
          <AreaAnnotation
            endDate={event.endDate as string}
            key={event.id}
            startDate={event.startDate as string}
            {...props}
          />
        );
      })}
    </>
  );
};

export default EventAnnotations;
