import axios, { AxiosResponse, CancelToken } from 'axios';

import { map, pick } from 'ramda';
import { resourcesEndpoint } from '../../../../api/endpoint';
import { Resource } from '../../../../models';

const disacknowledgeEndpoint = `${resourcesEndpoint}/disacknowledge`;

interface DisacknowledgeParams {
  disacknowledgeAttachedResources?: boolean;
}

interface ResourcesWithDisacknowledgeParams {
  resources: Array<Resource>;
  params: DisacknowledgeParams;
}

const disacknowledgeResources = (cancelToken: CancelToken) => ({
  resources,
  params,
}: ResourcesWithDisacknowledgeParams): Promise<Array<AxiosResponse>> => {
  return axios.post(
    disacknowledgeEndpoint,
    {
      resources: map(pick(['type', 'id', 'parent']), resources),
      disacknowledgement: {
        with_services: params.disacknowledgeAttachedResources,
      },
    },
    { cancelToken },
  );
};

export { disacknowledgeResources };
