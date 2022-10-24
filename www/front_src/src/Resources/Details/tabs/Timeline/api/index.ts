import { CancelToken } from 'axios';

import {
  buildListingEndpoint,
  ListingModel,
  getData,
  ListingParameters
} from '@centreon/ui';

import { TimelineEvent } from '../models';

interface ListTimeLineEventsProps {
  endpoint: string;
  parameters: ListingParameters;
}

const buildListTimelineEventsEndpoint = ({
  endpoint,
  parameters
}: ListTimeLineEventsProps): string =>
  buildListingEndpoint({
    baseEndpoint: endpoint,
    parameters
  });

const listTimelineEvents =
  (cancelToken: CancelToken) =>
  ({
    endpoint,
    parameters
  }: ListTimeLineEventsProps): Promise<ListingModel<TimelineEvent>> => {
    return getData<ListingModel<TimelineEvent>>(cancelToken)({
      endpoint: buildListTimelineEventsEndpoint({ endpoint, parameters })
    });
  };

export { listTimelineEvents, buildListTimelineEventsEndpoint };
