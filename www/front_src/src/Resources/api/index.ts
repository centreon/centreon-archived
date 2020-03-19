import axios, { AxiosRequestConfig, AxiosResponse } from 'axios';

import {
  buildResourcesEndpoint,
  serviceAcknowledgementEndpoint,
  hostAcknowledgementEndpoint,
  userEndpoint,
} from './endpoint';
import { ResourceListing, User } from '../models';

const getData = <TData>({ endpoint, requestParams }): Promise<TData> =>
  axios.get(endpoint, requestParams).then(({ data }) => data);

const listResources = (
  endpointParams,
  requestParams: AxiosRequestConfig = {},
): Promise<ResourceListing> =>
  getData({ endpoint: buildResourcesEndpoint(endpointParams), requestParams });

interface ResourceParams {
  resource_id: string;
  comment: string;
  is_notify_contacts: boolean;
  is_persistent_comment: boolean;
  is_sticky: boolean;
  parent_resource_id?: string;
}

const toResourceParams = ({ id, comment, notify, parent }): ResourceParams => ({
  comment,
  is_notify_contacts: notify,
  is_persistent_comment: true,
  is_sticky: true,
  resource_id: id,
  parent_resource_id: parent?.id,
});

const acknowledgeResources = ({
  resources,
  cancelToken,
}): Promise<Array<AxiosResponse>> => {
  const getResourceParamsForType = (resourceType): Array<ResourceParams> =>
    resources.filter(({ type }) => type === resourceType).map(toResourceParams);

  return axios.all(
    [
      {
        params: getResourceParamsForType('host'),
        endpoint: hostAcknowledgementEndpoint,
      },
      {
        params: getResourceParamsForType('service'),
        endpoint: serviceAcknowledgementEndpoint,
      },
    ]
      .filter(({ params }) => params.length > 0)
      .map(({ endpoint, params }) =>
        axios.post(endpoint, params, { cancelToken }),
      ),
  );
};

const getUser = (cancelToken): Promise<User> =>
  getData({ endpoint: userEndpoint, requestParams: cancelToken });

export { acknowledgeResources, listResources, getData, getUser };
