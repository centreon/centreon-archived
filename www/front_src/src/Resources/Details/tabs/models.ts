import { ResourceEndpoints } from '../../models';

export type TabEndpoints = Omit<ResourceEndpoints, 'details'>;

export type GraphEndpoints = Pick<
  ResourceEndpoints,
  'statusGraph' | 'performanceGraph'
>;
