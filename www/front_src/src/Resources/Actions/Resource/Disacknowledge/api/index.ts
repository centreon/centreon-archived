import axios, { AxiosResponse, CancelToken } from 'axios';

import { map, pick } from 'ramda';
import { resourcesEndpoint } from '../../../../api/endpoint';
import { Resource } from '../../../../models';

const disacknowledgeEndpoint = `${resourcesEndpoint}/disacknowledge`;

interface ResourcesWithDisacknowledgeParams {
  resources: Array<Resource>;
  disacknowledgeAttachedResources: boolean;
}

const disacknowledgeResources = (cancelToken: CancelToken) => ({
  resources,
  disacknowledgeAttachedResources,
}: ResourcesWithDisacknowledgeParams): Promise<Array<AxiosResponse>> => {
  return axios.post(
    disacknowledgeEndpoint,
    {
      resources: map(pick(['type', 'id', 'parent']), resources),
      disacknowledgement: {
        with_services: disacknowledgeAttachedResources,
      },
    },
    { cancelToken },
  );
};

export { disacknowledgeResources, disacknowledgeEndpoint };
