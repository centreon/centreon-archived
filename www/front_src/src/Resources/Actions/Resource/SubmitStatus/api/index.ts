import axios, { AxiosResponse, CancelToken } from 'axios';
import { equals } from 'ramda';

import { resourcesEndpoint } from '../../../../api/endpoint';
import { Resource, ResourceCategory, ResourceType } from '../../../../models';

const submitStatusEndpoint = `${resourcesEndpoint}/submit`;

interface ResourceWithSubmitStatusParams {
  output: string;
  performanceData: string;
  resource: Resource;
  statusId: number;
}

const submitResourceStatus =
  (cancelToken: CancelToken) =>
  ({
    resource,
    statusId,
    output,
    performanceData,
  }: ResourceWithSubmitStatusParams): Promise<Array<AxiosResponse>> => {
    return axios.post(
      submitStatusEndpoint,
      {
        resources: [
          {
            id: equals(resource.type, ResourceType.anomalydetection)
              ? resource.service_id
              : resource.id,
            output,
            parent: resource?.parent ? { id: resource.parent.id } : null,
            performance_data: performanceData,
            status: statusId,
            type: ResourceCategory[resource.type],
          },
        ],
      },
      { cancelToken },
    );
  };

export { submitResourceStatus, submitStatusEndpoint };
