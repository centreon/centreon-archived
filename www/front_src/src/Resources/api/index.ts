import axios, { AxiosRequestConfig, AxiosResponse } from 'axios';

import {
  buildResourcesEndpoint,
  serviceAcknowledgementEndpoint,
  hostAcknowledgementEndpoint,
} from './endpoint';
import { ResourceListing } from '../models';

const getData = <TData>({ endpoint, requestParams }): Promise<TData> =>
  axios.get(endpoint, requestParams).then(({ data }) => data);

const listResources = (
  endpointParams,
  requestParams: AxiosRequestConfig = {},
): Promise<ResourceListing> =>
  getData({ endpoint: buildResourcesEndpoint(endpointParams), requestParams });

const getResourceParams = ({ id, comment, notify, parent_id }) => ({
  comment,
  is_notify: notify,
  is_persistent_comment: true,
  is_sticky: true,
  resource_id: id,
  parent_resource_id: parent_id,
});

const acknowledgeResources = ({
  resources,
  cancelToken,
}): Promise<Array<AxiosResponse>> => {
  const hostParams = resources
    .filter(({ type }) => type === 'host')
    .map((resource) => getResourceParams(resource));

  const serviceParams = resources
    .filter(({ type }) => type === 'service')
    .map((resource) => getResourceParams(resource));

  return axios.all(
    [
      { params: hostParams, endpoint: hostAcknowledgementEndpoint },
      { params: serviceParams, endpoint: serviceAcknowledgementEndpoint },
    ]
      .filter(({ params }) => params.length > 0)
      .map(({ endpoint, params }) =>
        axios.post(endpoint, params, { cancelToken }),
      ),
  );
};

export { acknowledgeResources, listResources, getData };
