import { ListingModel } from '@centreon/ui';

import { Status } from '../../../models';

export interface Service {
  id: number;
  status?: Status;
  output: string;
  name: string;
}

export type ServiceListing = ListingModel<Service>;
