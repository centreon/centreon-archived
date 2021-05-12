import axios, { AxiosResponse, CancelToken } from 'axios';
import { pick } from 'ramda';

import { resourcesEndpoint } from '../../../../api/endpoint';
import { Resource } from '../../../../models';

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
            ...pick(['type', 'id', 'parent'], resource),
            output,
            performance_data: performanceData,
            status: statusId,
          },
        ],
      },
      { cancelToken },
    );
  };

export { submitResourceStatus, submitStatusEndpoint };
