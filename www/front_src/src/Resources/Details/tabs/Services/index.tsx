import * as React from 'react';

import { useRequest, ListingModel } from '@centreon/ui';

import { TabProps } from '..';
import { ResourceContext, useResourceContext } from '../../../Context';
import { listResources } from '../../../Listing/api';
import { Resource } from '../../../models';
import InfiniteScroll from '../../InfiniteScroll';
import memoizeComponent from '../../../memoizedComponent';

import ServiceList from './List';
import LoadingSkeleton from './LoadingSkeleton';

type ServicesTabContentProps = TabProps &
  Pick<
    ResourceContext,
    'selectResource' | 'tabParameters' | 'setServicesTabParameters'
  >;

const ServicesTabContent = ({
  details,
  selectResource,
}: ServicesTabContentProps): JSX.Element => {
  const { sendRequest, sending } = useRequest({
    request: listResources,
  });

  const limit = 30;

  const sendListingRequest = ({
    atPage,
  }: {
    atPage?: number;
  }): Promise<ListingModel<Resource>> => {
    return sendRequest({
      limit,
      page: atPage,
      resourceTypes: ['service'],
      search: {
        conditions: [
          {
            field: 'h.name',
            values: {
              $eq: details?.name,
            },
          },
        ],
      },
    });
  };

  return (
    <InfiniteScroll<Resource>
      details={details}
      limit={limit}
      loading={sending}
      loadingSkeleton={<LoadingSkeleton />}
      preventReloadWhen={details?.type !== 'host'}
      sendListingRequest={sendListingRequest}
    >
      {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
        return (
          <ServiceList
            infiniteScrollTriggerRef={infiniteScrollTriggerRef}
            services={entities}
            onSelectService={selectResource}
          />
        );
      }}
    </InfiniteScroll>
  );
};

const MemoizedServiceTabContent = memoizeComponent<ServicesTabContentProps>({
  Component: ServicesTabContent,
  memoProps: ['details', 'tabParameters'],
});

const ServicesTab = ({ details }: TabProps): JSX.Element => {
  const { selectResource, tabParameters, setServicesTabParameters } =
    useResourceContext();

  return (
    <MemoizedServiceTabContent
      details={details}
      selectResource={selectResource}
      setServicesTabParameters={setServicesTabParameters}
      tabParameters={tabParameters}
    />
  );
};

export default ServicesTab;
