import * as React from 'react';

import { ScaleTime } from 'd3-scale';

import { TimelineEvent } from '../../../../Details/tabs/Timeline/models';
import useAnnotations, { Annotations } from '../useAnnotations';
import { AnnotationsContext } from '../Context';

import CommentAnnotations from './Line/Comments';
import AcknowledgementAnnotations from './Line/Acknowledgement';
import DowntimeAnnotations from './Area/Downtime';

export interface Props {
  xScale: ScaleTime<number, number>;
  timeline: Array<TimelineEvent>;
  graphHeight: number;
}

const Annotations = ({ xScale, timeline, graphHeight }: Props): JSX.Element => {
  const annotations = useAnnotations();
  const props = {
    xScale,
    timeline,
    graphHeight,
  };

  return (
    <AnnotationsContext.Provider value={annotations}>
      <CommentAnnotations {...props} />
      <AcknowledgementAnnotations {...props} />
      <DowntimeAnnotations {...props} />
    </AnnotationsContext.Provider>
  );
};

export default Annotations;
