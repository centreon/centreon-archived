import { ListingModel } from '@centreon/ui';
import { Status } from '../../../models';

export interface Service {
  id: number;
  status: Status;
  output: string;
  name: string;
  duration?: string;
}

export type ServiceListing = ListingModel<Service>;
