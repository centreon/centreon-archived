import * as React from 'react';

import { ScaleTime } from 'd3-scale';

import { TimelineEvent } from '../../../../Details/tabs/Timeline/models';

import CommentAnnotations from './Line/Comments';
import AcknowledgementAnnotations from './Line/Acknowledgement';
import DowntimeAnnotations from './Area/Downtime';

export interface Props {
  graphHeight: number;
  timeline: Array<TimelineEvent>;
  xScale: ScaleTime<number, number>;
}

const Annotations = ({ xScale, timeline, graphHeight }: Props): JSX.Element => {
  const props = {
    graphHeight,
    timeline,
    xScale,
  };

  return (
    <>
      <CommentAnnotations {...props} />
      <AcknowledgementAnnotations {...props} />
      <DowntimeAnnotations {...props} />
    </>
  );
};

export default Annotations;
