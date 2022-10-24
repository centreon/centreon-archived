import { path } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { useRequest } from '@centreon/ui';

import InfiniteScroll from '../../InfiniteScroll';
import LoadingSkeleton from '../Services/LoadingSkeleton';
import { detailsAtom } from '../../detailsAtoms';

import { MetaServiceMetricListing } from './models';
import { listMetaServiceMetrics } from './api';
import { metaServiceMetricListingDecoder } from './api/decoders';
import Metrics from './Metrics';

const limit = 30;

const MetricsTab = (): JSX.Element => {
  const { sendRequest, sending } = useRequest<MetaServiceMetricListing>({
    decoder: metaServiceMetricListingDecoder,
    request: listMetaServiceMetrics
  });

  const details = useAtomValue(detailsAtom);

  const endpoint = path(['links', 'endpoints', 'metrics'], details);

  const sendListingRequest = ({
    atPage
  }: {
    atPage?: number;
  }): Promise<MetaServiceMetricListing> => {
    return sendRequest({
      endpoint,
      parameters: {
        limit,
        page: atPage
      }
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
          />
        );
      }}
    </InfiniteScroll>
  );
};

export default MetricsTab;
