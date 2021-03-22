import * as React from 'react';

import { equals, last } from 'ramda';

import DetailsCard from '../Details/DetailsCard';
import DetailsLine from '../Details/DetailsCard/DetailsLine';
import ServiceCard from '../Details/ServiceCard';
import { useResourceContext } from '../../../Context';
import { labelCalculationType } from '../../../translatedLabels';

import { MetaServiceMetric } from './models';

interface Props {
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  metrics: Array<MetaServiceMetric>;
  calculationType: string;
}

const Metrics = ({
  infiniteScrollTriggerRef,
  metrics,
  calculationType,
}: Props): JSX.Element => {
  const { selectResource } = useResourceContext();

  return (
    <>
      <DetailsCard
        title={labelCalculationType}
        line={<DetailsLine line={calculationType} />}
      />
      {metrics.map((metric) => {
        const isLastMetric = equals(last(metrics), metric);

        const { id, name, resource, unit, value } = metric;

        return (
          <div key={id}>
            <ServiceCard
              name={resource.name}
              status={resource.status}
              information={name}
              subInformation={`${value} (${unit})`}
              onSelect={() => selectResource(resource)}
            />
            {isLastMetric && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </>
  );
};

export default Metrics;
