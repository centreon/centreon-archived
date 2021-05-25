import { CancelToken } from 'axios';

import { getData, buildListingEndpoint } from '@centreon/ui';

import { monitoringEndpoint } from '../../../../api/endpoint';
import { ServiceListing } from '../models';

const hostEndpoint = `${monitoringEndpoint}/hosts`;

const getServicesEndpoint = (hostId: number): string => {
  return `${hostEndpoint}/${hostId}/services`;
};

const buildListServicesEndpoint = ({ parameters, hostId }): string => {
  return buildListingEndpoint({
    baseEndpoint: getServicesEndpoint(hostId),
    parameters,
  });
};

const listServices =
  (cancelToken: CancelToken) =>
  (hostId: number): Promise<ServiceListing> => {
    return getData<ServiceListing>(cancelToken)(
      buildListServicesEndpoint({
        hostId,
        parameters: {
          limit: 100,
        },
      }),
    );
  };

export { listServices };
