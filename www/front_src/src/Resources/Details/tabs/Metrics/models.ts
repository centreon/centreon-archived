import { ListingModel } from '@centreon/ui';

import { Resource } from '../../../models';

export interface MetaServiceMetric {
  id: number;
  name: string;
  unit: string;
  value: number;
  resource: Resource;
}

export type MetaServiceMetricListing = ListingModel<MetaServiceMetric>;
