import {
  buildListingEndpoint,
  ListingModel,
  getData,
  ListingParameters,
} from '@centreon/ui';
import { CancelToken } from 'axios';
import { TimelineEvent } from '../models';

interface Props {
  endpoint: string;
  parameters: ListingParameters;
}

const buildListTimelineEventsEndpoint = ({
  endpoint,
  parameters,
}: Props): string =>
  buildListingEndpoint({
    baseEndpoint: endpoint,
    parameters,
  });

const listTimelineEvents =
  (cancelToken: CancelToken) =>
  ({ endpoint, parameters }: Props): Promise<ListingModel<TimelineEvent>> => {
    return getData<ListingModel<TimelineEvent>>(cancelToken)(
      buildListTimelineEventsEndpoint({ endpoint, parameters }),
    );
  };

export { listTimelineEvents, buildListTimelineEventsEndpoint };
