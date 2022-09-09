import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import { useRequest, ListingModel } from '@centreon/ui';

import { listResources } from '../../../Listing/api';
import { Resource } from '../../../models';
import InfiniteScroll from '../../InfiniteScroll';
import { detailsAtom, selectResourceDerivedAtom } from '../../detailsAtoms';

import ServiceList from './List';
import LoadingSkeleton from './LoadingSkeleton';

const ServicesTab = (): JSX.Element => {
  const { sendRequest, sending } = useRequest({
    request: listResources,
  });

  const details = useAtomValue(detailsAtom);
  const selectResource = useUpdateAtom(selectResourceDerivedAtom);

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

export default ServicesTab;
