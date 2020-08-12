import { buildListingEndpoint, ListingModel, getData } from '@centreon/ui';
import { TimelineEvent } from '../models';

const buildListTimelineEventsEndpoint = ({ endpoint, parameters }): string =>
  buildListingEndpoint({
    baseEndpoint: endpoint,
    parameters,
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
