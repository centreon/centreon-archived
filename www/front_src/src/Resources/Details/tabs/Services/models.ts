import { ListingModel } from '@centreon/ui';

import { Status } from '../../../models';

export interface Service {
  duration?: string;
  id: number;
  name: string;
  output: string;
  status: Status;
}

export type ServiceListing = ListingModel<Service>;
