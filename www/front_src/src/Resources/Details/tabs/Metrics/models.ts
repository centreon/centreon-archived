import { ListingModel } from '@centreon/ui';

import { CompactResource } from '../../../models';

export interface MetaServiceMetric {
  id: number;
  name: string;
  unit: string;
  value: number;
  resource: CompactResource;
}

export type MetaServiceMetricListing = ListingModel<MetaServiceMetric>;
