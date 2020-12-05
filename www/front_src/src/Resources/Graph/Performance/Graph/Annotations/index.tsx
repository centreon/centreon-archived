import * as React from 'react';

import { ScaleTime } from 'd3-scale';

import { TimelineEvent } from '../../../../Details/tabs/Timeline/models';

import CommentAnnotations from './Events/Comments';
import AcknowledgementAnnotations from './Events/Acknowledgement';

export interface Props {
  xScale: ScaleTime<number, number>;
  timeline: Array<TimelineEvent>;
  graphHeight: number;
}

const Annotations = ({ xScale, timeline, graphHeight }: Props): JSX.Element => {
  const props = {
    xScale,
    timeline,
    graphHeight,
  };

  return (
    <>
      <CommentAnnotations {...props} />
      <AcknowledgementAnnotations {...props} />
    </>
  );
};

export default Annotations;
