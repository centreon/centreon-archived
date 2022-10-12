import axios, { AxiosResponse, CancelToken } from 'axios';
import { equals } from 'ramda';

import { resourcesEndpoint } from '../../../../api/endpoint';
import { Resource, ResourceType, ResourceCategory } from '../../../../models';

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
    const payload = resources.map(({ type, id, parent, service_id }) => ({
      id: equals(type, ResourceType.anomalydetection) ? service_id : id,
      parent: parent ? { id: parent?.id } : null,
      type: ResourceCategory[type],
    }));

    return axios.delete(disacknowledgeEndpoint, {
      cancelToken,
      data: {
        disacknowledgement: {
          with_services: disacknowledgeAttachedResources,
        },
        resources: payload,
      },
    });
  };

export { disacknowledgeResources, disacknowledgeEndpoint };
