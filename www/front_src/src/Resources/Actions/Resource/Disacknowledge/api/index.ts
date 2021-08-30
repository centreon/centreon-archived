import axios, { AxiosResponse, CancelToken } from 'axios';
import { map, pick } from 'ramda';

import { resourcesEndpoint } from '../../../../api/endpoint';
import { Resource } from '../../../../models';

const disacknowledgeEndpoint = `${resourcesEndpoint}/acknowledgements`;

interface ResourcesWithDisacknowledgeParams {
  disacknowledgeAttachedResources: boolean;
  resources: Array<Resource>;
}

const disacknowledgeResources =
  (cancelToken: CancelToken) =>
  ({
    resources,
    disacknowledgeAttachedResources,
  }: ResourcesWithDisacknowledgeParams): Promise<Array<AxiosResponse>> => {
    return axios.delete(disacknowledgeEndpoint, {
      cancelToken,
      data: {
        disacknowledgement: {
          with_services: disacknowledgeAttachedResources,
        },
        resources: map(pick(['type', 'id', 'name', 'parent']), resources),
      },
    });
  };

export { disacknowledgeResources, disacknowledgeEndpoint };
