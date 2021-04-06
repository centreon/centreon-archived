import * as React from 'react';

import { path } from 'ramda';

import { useRequest } from '@centreon/ui';

import { TabProps } from '..';
import InfiniteScroll from '../../InfiniteScroll';
import LoadingSkeleton from '../Services/LoadingSkeleton';
import memoizeComponent from '../../../memoizedComponent';
import { useResourceContext, ResourceContext } from '../../../Context';

import { MetaServiceMetricListing } from './models';
import { listMetaServiceMetrics } from './api';
import { metaServiceMetricListingDecoder } from './api/decoders';
import Metrics from './Metrics';

const limit = 30;

type MetricsTabContentProps = TabProps &
  Pick<ResourceContext, 'selectResource'>;

const MetricsTabContent = ({
  details,
  selectResource,
}: MetricsTabContentProps): JSX.Element => {
  const endpoint = path(['links', 'endpoints', 'metrics'], details);

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
      loadingSkeleton={<LoadingSkeleton />}
      loading={sending}
      limit={limit}
      preventReloadWhen={details?.type !== 'metaservice'}
    >
      {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
        return (
          <Metrics
            metrics={entities}
            infiniteScrollTriggerRef={infiniteScrollTriggerRef}
            selectResource={selectResource}
          />
        );
      }}
    </InfiniteScroll>
  );
};

const MemoizedMetricsTabContent = memoizeComponent<MetricsTabContentProps>({
  memoProps: ['details'],
  Component: MetricsTabContent,
});

const MetricsTab = ({ details }: TabProps): JSX.Element => {
  const { selectResource } = useResourceContext();

  return (
    <MemoizedMetricsTabContent
      details={details}
      selectResource={selectResource}
    />
  );
};

export default MetricsTab;
