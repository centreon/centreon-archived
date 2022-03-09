import { CancelToken } from 'axios';

import {
  buildListingEndpoint,
  ListingModel,
  getData,
  ListingParameters,
} from '@centreon/ui';

interface ListNotificationProps {
  endpoint: string;
  parameters: ListingParameters;
}

const buildNotificationEndpoint = ({
  endpoint,
  parameters,
}: ListNotificationProps): string =>
  buildListingEndpoint({
    baseEndpoint: endpoint,
    parameters,
  });
