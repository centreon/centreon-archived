import * as React from 'react';

import { equals, last } from 'ramda';

import { Resource } from '../../../models';
import ServiceCard from '../Details/ServiceCard';

interface Props {
  services: Array<Resource>;
  onSelectService: (service: Resource) => void;
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
}

const ServiceList = ({
  services,
  onSelectService,
  infiniteScrollTriggerRef,
}: Props): JSX.Element => {
  return (
    <>
      {services.map((service) => {
        const isLastService = equals(last(services), service);
        const { id, name, status, information, duration } = service;

        return (
          <div key={id}>
            <ServiceCard
              name={name}
              status={status}
              information={information}
              subInformation={duration}
              onSelect={() => onSelectService(service)}
            />
            {isLastService && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </>
  );
};

export default ServiceList;
