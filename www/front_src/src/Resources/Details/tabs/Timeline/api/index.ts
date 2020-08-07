import { buildListingEndpoint, ListingModel, getData } from '@centreon/ui';
import { TimelineEvent } from '../models';

const buildListTimelineEventsEndpoint = ({ endpoint, options }): string =>
  buildListingEndpoint({
    baseEndpoint: endpoint,
    options,
  });

const listTimelineEvents = (cancelToken) => ({
  endpoint,
  options,
}): Promise<ListingModel<TimelineEvent>> => {
  return getData<ListingModel<TimelineEvent>>(cancelToken)(
    buildListTimelineEventsEndpoint({ endpoint, options }),
  );
};

export { listTimelineEvents, buildListTimelineEventsEndpoint };
