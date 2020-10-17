import { CancelToken } from 'axios';

import {
  buildListingEndpoint,
  ListingModel,
  getData,
  SearchParameter,
} from '@centreon/ui';
import { TimelineEvent } from '../models';

interface Parameters {
  search: SearchParameter | undefined;
  page: number;
  limit: number;
}

interface TimelineParams {
  endpoint: string;
  parameters: Parameters;
}

const buildListTimelineEventsEndpoint = ({
  endpoint,
  parameters,
}: TimelineParams): string =>
  buildListingEndpoint({
    baseEndpoint: endpoint,
    parameters,
  });

const listTimelineEvents = (cancelToken: CancelToken) => ({
  endpoint,
  parameters,
}: TimelineParams): Promise<ListingModel<TimelineEvent>> => {
  return getData<ListingModel<TimelineEvent>>(cancelToken)(
    buildListTimelineEventsEndpoint({ endpoint, parameters }),
  );
};

export { listTimelineEvents, buildListTimelineEventsEndpoint };
