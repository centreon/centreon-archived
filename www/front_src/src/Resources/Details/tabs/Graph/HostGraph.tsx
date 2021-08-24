import * as React from 'react';

import { isNil } from 'ramda';

import { ListingModel, useMemoComponent, useRequest } from '@centreon/ui';

import { TabProps } from '..';
import TimePeriodButtonGroup from '../../../Graph/Performance/TimePeriods';
import { Resource } from '../../../models';
import InfiniteScroll from '../../InfiniteScroll';
import ServiceGraphs from '../Services/Graphs';
import LoadingSkeleton from '../Timeline/LoadingSkeleton';
import { listResources } from '../../../Listing/api';

const HostGraph = ({ details }: TabProps): JSX.Element => {
  const { sendRequest, sending } = useRequest({
    request: listResources,
  });

  const limit = 6;

  const sendListingRequest = ({
    atPage,
  }: {
    atPage?: number;
  }): Promise<ListingModel<Resource>> => {
    return sendRequest({
      limit,
      onlyWithPerformanceData: true,
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

  const loading = isNil(details) || sending;

  return (
    <InfiniteScroll<Resource>
      details={details}
      filter={<TimePeriodButtonGroup disabled={loading} />}
      limit={limit}
      loading={sending}
      loadingSkeleton={<LoadingSkeleton />}
      preventReloadWhen={isNil(details)}
      sendListingRequest={sendListingRequest}
    >
      {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
        return (
          <ServiceGraphs
            infiniteScrollTriggerRef={infiniteScrollTriggerRef}
            services={entities}
          />
        );
      }}
    </InfiniteScroll>
  );
};

export default HostGraph;
