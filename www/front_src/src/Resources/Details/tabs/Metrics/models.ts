import { ListingModel } from '@centreon/ui';

import { Resource } from '../../../models';

export interface MetaServiceMetric {
  id: number;
  name: string;
  resource: Resource;
  unit: string;
  value: number;
}

export type MetaServiceMetricListing = ListingModel<MetaServiceMetric>;
