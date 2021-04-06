import * as React from 'react';

import { equals, last } from 'ramda';

import { Resource, Status } from '../../../models';
import ServiceCard from '../Details/ServiceCard';

interface Props {
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  onSelectService: (service: Resource) => void;
  services: Array<Resource>;
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
              information={information}
              name={name}
              status={status as Status}
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
