import { buildListingEndpoint } from '@centreon/ui';

import { monitoringServersEndpoint } from './endpoints';

export const buildMonitoringServersEndpoint = (limit: number): string =>
  buildListingEndpoint({
    baseEndpoint: monitoringServersEndpoint,
    parameters: {
      limit,
      page: 1,
      search: {
        regex: {
          fields: ['is_activate'],
          value: `is_activate:1`,
        },
      },
    },
  });
