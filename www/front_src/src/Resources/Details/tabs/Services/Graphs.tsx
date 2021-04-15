import * as React from 'react';

import { path, isNil, equals, last, pipe, not } from 'ramda';

import { Resource } from '../../../models';
import ExportablePerformanceGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import { CustomTimePeriod, TimePeriod } from '../Graph/models';
import { AdjustTimePeriodProps } from '../../../Graph/Performance/models';

const MemoizedPerformanceGraph = React.memo(
  ExportablePerformanceGraphWithTimeline,
  (prevProps, nextProps) => {
    const prevResource = prevProps.resource;
    const nextResource = nextProps.resource;
    const prevPeriodQueryParameters = prevProps.periodQueryParameters;
    const nextPeriodQueryParameters = nextProps.periodQueryParameters;
    const prevTooltipPosition = prevProps.tooltipPosition;
    const nextTooltipPosition = nextProps.tooltipPosition;
    const prevSelectedTimePeriod = prevProps.selectedTimePeriod;
    const nextSelectedTimePeriod = nextProps.selectedTimePeriod;

    return (
      equals(prevResource?.id, nextResource?.id) &&
      equals(prevPeriodQueryParameters, nextPeriodQueryParameters) &&
      equals(prevTooltipPosition, nextTooltipPosition) &&
      equals(prevSelectedTimePeriod, nextSelectedTimePeriod)
    );
  },
);

interface Props {
  adjustTimePeriod: (props: AdjustTimePeriodProps) => void;
  customTimePeriod: CustomTimePeriod;
  getIntervalDates: () => [string, string];
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  periodQueryParameters: string;
  resourceDetailsUpdated: boolean;
  selectedTimePeriod: TimePeriod | null;
  services: Array<Resource>;
}

const ServiceGraphs = ({
  services,
  infiniteScrollTriggerRef,
  periodQueryParameters,
  getIntervalDates,
  selectedTimePeriod,
  customTimePeriod,
  adjustTimePeriod,
  resourceDetailsUpdated,
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
              limitLegendRows
              adjustTimePeriod={adjustTimePeriod}
              customTimePeriod={customTimePeriod}
              getIntervalDates={getIntervalDates}
              graphHeight={120}
              periodQueryParameters={periodQueryParameters}
              resource={service}
              resourceDetailsUpdated={resourceDetailsUpdated}
              selectedTimePeriod={selectedTimePeriod}
              tooltipPosition={tooltipPosition}
              onTooltipDisplay={setTooltipPosition}
            />
            {isLastService && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </>
  );
};

export default ServiceGraphs;
