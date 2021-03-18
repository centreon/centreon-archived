import * as React from 'react';

import { path } from 'ramda';

import { Skeleton } from '@material-ui/lab';

import { useRequest } from '@centreon/ui';

import { TabProps } from '..';
import InfiniteScroll from '../../InfiniteScroll';

import { MetaServiceMetricListing } from './models';
import { listMetaServiceMetrics } from './api';
import { metaServiceMetricListingDecoder } from './api/decoders';
import Metrics from './Metrics';

const limit = 30;

const MetricsTab = ({ details }: TabProps): JSX.Element => {
  // const endpoint = path(['links', 'endpoints', 'metrics'], details);

  const endpoint = 'http://localhost:5000/api/metrics';

  const { sendRequest, sending } = useRequest<MetaServiceMetricListing>({
    request: listMetaServiceMetrics,
    decoder: metaServiceMetricListingDecoder,
  });

  const sendListingRequest = ({
    atPage,
  }: {
    atPage?: number;
  }): Promise<MetaServiceMetricListing> => {
    return sendRequest({
      endpoint,
      parameters: {
        page: atPage,
        limit,
      },
    });
  };

  return (
    <InfiniteScroll
      details={details}
      sendListingRequest={sendListingRequest}
      loadingSkeleton={<Skeleton />}
      loading={sending}
      limit={limit}
    >
      {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
        return (
          <Metrics
            metrics={entities}
            infiniteScrollTriggerRef={infiniteScrollTriggerRef}
          />
        );
      }}
    </InfiniteScroll>
  );
};

export default MetricsTab;
