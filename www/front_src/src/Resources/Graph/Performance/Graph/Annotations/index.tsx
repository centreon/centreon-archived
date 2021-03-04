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

export interface Annotations {
  annotationHovered: TimelineEvent | null;
  setAnnotationHovered: React.Dispatch<
    React.SetStateAction<TimelineEvent | null>
  >;
}

export const AnnotationsContext = React.createContext<Annotations | undefined>(
  undefined,
);

const Annotations = ({ xScale, timeline, graphHeight }: Props): JSX.Element => {
  const [
    annotationHovered,
    setAnnotationHovered,
  ] = React.useState<TimelineEvent | null>(null);
  const props = {
    xScale,
    timeline,
    graphHeight,
  };

  return (
    <AnnotationsContext.Provider
      value={{
        annotationHovered,
        setAnnotationHovered,
      }}
    >
      <CommentAnnotations {...props} />
      <AcknowledgementAnnotations {...props} />
      <DowntimeAnnotations {...props} />
    </AnnotationsContext.Provider>
  );
};

export default Annotations;
