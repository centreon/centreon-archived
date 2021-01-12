import * as React from 'react';

import { path, isNil, equals, last, pipe, not } from 'ramda';

import { Resource } from '../../../models';
import ExportablePerformanceGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import { TimePeriod } from '../Graph/models';

const MemoizedPerformanceGraph = React.memo(
  ExportablePerformanceGraphWithTimeline,
  (prevProps, nextProps) => {
    const prevResource = prevProps.resource;
    const nextResource = nextProps.resource;
    const prevPeriodQueryParameters = prevProps.periodQueryParameters;
    const nextPeriodQueryParameters = nextProps.periodQueryParameters;
    const prevTooltipPosition = prevProps.tooltipPosition;
    const nextTooltipPosition = nextProps.tooltipPosition;

    return (
      equals(prevResource?.id, nextResource?.id) &&
      equals(prevPeriodQueryParameters, nextPeriodQueryParameters) &&
      equals(prevTooltipPosition, nextTooltipPosition)
    );
  },
);

interface Props {
  services: Array<Resource>;
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  periodQueryParameters: string;
  getIntervalDates: () => [string, string];
  selectedTimePeriod: TimePeriod;
}

const ServiceGraphs = ({
  services,
  infiniteScrollTriggerRef,
  periodQueryParameters,
  getIntervalDates,
  selectedTimePeriod,
}: Props): JSX.Element => {
  const [tooltipPosition, setTooltipPosition] = React.useState<
    [number, number]
  >();

  const servicesWithGraph = services.filter(
    pipe(path(['links', 'endpoints', 'performance_graph']), isNil, not),
  );

  return (
    <>
      {servicesWithGraph.map((service) => {
        const { id } = service;
        const isLastService = equals(last(servicesWithGraph), service);

        return (
          <div key={id}>
            <MemoizedPerformanceGraph
              resource={service}
              graphHeight={120}
              periodQueryParameters={periodQueryParameters}
              getIntervalDates={getIntervalDates}
              selectedTimePeriod={selectedTimePeriod}
              onTooltipDisplay={setTooltipPosition}
              tooltipPosition={tooltipPosition}
            />
            {isLastService && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </>
  );
};

export default ServiceGraphs;
