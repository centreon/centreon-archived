import * as React from 'react';

import { path, isNil, equals, last, pipe, not } from 'ramda';

import { makeStyles } from '@material-ui/styles';

import { Resource } from '../../../models';
import ExportablePerformanceGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import useMousePosition, {
  MousePositionContext,
} from '../../../Graph/Performance/ExportableGraphWithTimeline/useMousePosition';

const MemoizedPerformanceGraph = React.memo(
  ExportablePerformanceGraphWithTimeline,
  (prevProps, nextProps) => {
    const prevResource = prevProps.resource;
    const nextResource = nextProps.resource;

    return equals(prevResource?.id, nextResource?.id);
  },
);

interface Props {
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  services: Array<Resource>;
}

const useStyles = makeStyles({
  serviceGraph: {
    display: 'contents',
  },
});

const ServiceGraphs = ({
  services,
  infiniteScrollTriggerRef,
}: Props): JSX.Element => {
  const classes = useStyles();
  const mousePositionProps = useMousePosition();

  const servicesWithGraph = services.filter(
    pipe(path(['links', 'endpoints', 'performance_graph']), isNil, not),
  );

  return (
    <>
      <MousePositionContext.Provider value={mousePositionProps}>
        {servicesWithGraph.map((service) => {
          const { id } = service;
          const isLastService = equals(last(servicesWithGraph), service);

          return (
            <div className={classes.serviceGraph} key={id}>
              <MemoizedPerformanceGraph
                limitLegendRows
                graphHeight={120}
                resource={service}
              />
              {isLastService && <div ref={infiniteScrollTriggerRef} />}
            </div>
          );
        })}
      </MousePositionContext.Provider>
    </>
  );
};

export default ServiceGraphs;
