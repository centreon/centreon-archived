import * as React from 'react';

import { path, isNil, equals, last, pipe, not } from 'ramda';

import { Resource } from '../../../models';
import ExportablePerformanceGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import { MousePosition } from '../../../Graph/Performance/Graph/useMetricsValue';

const MemoizedPerformanceGraph = React.memo(
  ExportablePerformanceGraphWithTimeline,
  (prevProps, nextProps) => {
    const prevResource = prevProps.resource;
    const nextResource = nextProps.resource;

    return (
      equals(prevResource?.id, nextResource?.id) &&
      equals(
        prevProps.resourceGraphMousePosition,
        nextProps.resourceGraphMousePosition,
      )
    );
  },
);

interface Props {
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  services: Array<Resource>;
}

export interface ResourceGraphMousePosition {
  mousePosition: MousePosition;
  resourceId: string | number;
}

const ServiceGraphs = ({
  services,
  infiniteScrollTriggerRef,
}: Props): JSX.Element => {
  const [resourceGraphMousePosition, setResourceGraphMousePosition] =
    React.useState<ResourceGraphMousePosition | null>(null);

  const servicesWithGraph = services.filter(
    pipe(path(['links', 'endpoints', 'performance_graph']), isNil, not),
  );

  return (
    <div
      style={{
        columnGap: '8px',
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(350px, auto))',
        rowGap: '8px',
      }}
    >
      {servicesWithGraph.map((service) => {
        const { id } = service;
        const isLastService = equals(last(servicesWithGraph), service);

        return (
          <div key={id}>
            <MemoizedPerformanceGraph
              limitLegendRows
              graphHeight={120}
              resource={service}
              resourceGraphMousePosition={resourceGraphMousePosition}
              updateResourceGraphMousePosition={setResourceGraphMousePosition}
            />
            {isLastService && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </div>
  );
};

export default ServiceGraphs;
