import axios, { AxiosResponse, CancelToken } from 'axios';

import { pick } from 'ramda';
import { resourcesEndpoint } from '../../../../api/endpoint';
import { Resource } from '../../../../models';

const submitStatusEndpoint = `${resourcesEndpoint}/submit`;

interface ResourceWithSubmitStatusParams {
  resource: Resource;
  statusId: number;
  output: string;
  performanceData: string;
}

const submitResourceStatus = (cancelToken: CancelToken) => ({
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
          ...pick(['type', 'id', 'parent'], resource),
          output,
          status: statusId,
          performance_data: performanceData,
        },
      ],
    },
    { cancelToken },
  );
};

export { submitResourceStatus, submitStatusEndpoint };
