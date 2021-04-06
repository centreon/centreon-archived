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
    decoder: metaServiceMetricListingDecoder,
    request: listMetaServiceMetrics,
  });

  const sendListingRequest = ({
    atPage,
  }: {
    atPage?: number;
  }): Promise<MetaServiceMetricListing> => {
    return sendRequest({
      endpoint,
      parameters: {
        limit,
        page: atPage,
      },
    });
  };

  return (
    <InfiniteScroll
      details={details}
      limit={limit}
      loading={sending}
      loadingSkeleton={<LoadingSkeleton />}
      preventReloadWhen={details?.type !== 'metaservice'}
      sendListingRequest={sendListingRequest}
    >
      {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
        return (
          <Metrics
            infiniteScrollTriggerRef={infiniteScrollTriggerRef}
            metrics={entities}
            selectResource={selectResource}
          />
        );
      }}
    </InfiniteScroll>
  );
};

const MemoizedMetricsTabContent = memoizeComponent<MetricsTabContentProps>({
  Component: MetricsTabContent,
  memoProps: ['details'],
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
