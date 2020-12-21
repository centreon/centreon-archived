import * as React from 'react';

import { ScaleTime } from 'd3-scale';

import { TimelineEvent } from '../../../../Details/tabs/Timeline/models';

import CommentAnnotations from './Line/Comments';
import AcknowledgementAnnotations from './Line/Acknowledgement';
import DowntimeAnnotations from './Area/Downtime';

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
      <DowntimeAnnotations {...props} />
    </>
  );
};

export default Annotations;
