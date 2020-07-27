import { buildListingEndpoint, ListingModel, getData } from '@centreon/ui';
import { TimelineEvent } from '../models';

const buildListTimelineEventsEndpoint = ({ endpoint, params }): string =>
  buildListingEndpoint({
    baseEndpoint: endpoint,
    params,
  });

const listTimelineEvents = (cancelToken) => ({
  endpoint,
  params,
}): Promise<ListingModel<TimelineEvent>> => {
  return getData<ListingModel<TimelineEvent>>(cancelToken)(
    buildListTimelineEventsEndpoint({ endpoint, params }),
  );
};

export { listTimelineEvents };
