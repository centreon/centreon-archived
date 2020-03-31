import axios, { AxiosRequestConfig, AxiosResponse } from 'axios';
import formatISO from 'date-fns/formatISO';

import {
  buildResourcesEndpoint,
  serviceAcknowledgementEndpoint,
  hostAcknowledgementEndpoint,
  serviceDowntimeEndpoint,
  hostDowntimeEndpoint,
  serviceCheckEndpoint,
  hostCheckEndpoint,
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

interface AcknowledgeParams {
  resource_id: number;
  comment: string;
  is_notify_contacts: boolean;
  is_persistent_comment: boolean;
  is_sticky: boolean;
  parent_resource_id?: number | null;
  with_services?: boolean;
}

const toAcknowledgeParams = ({
  id,
  comment,
  notify,
  parent,
  acknowledgeAttachedResources,
}): AcknowledgeParams => ({
  comment,
  is_notify_contacts: notify,
  is_persistent_comment: true,
  is_sticky: true,
  resource_id: id,
  parent_resource_id: parent?.id || null,
  with_services: acknowledgeAttachedResources,
});

const acknowledgeResources = ({
  resources,
  cancelToken,
}): Promise<Array<AxiosResponse>> => {
  const getResourceParamsForType = (resourceType): Array<AcknowledgeParams> =>
    resources
      .filter(({ type }) => type === resourceType)
      .map(toAcknowledgeParams);

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

interface DowntimeParams {
  comment: string;
  duration: string;
  end_time: string;
  is_fixed: boolean;
  parent_resource_id?: number | null;
  resource_id: number;
  start_time: string;
  with_services?: boolean;
}

const toDowntimeParams = ({
  comment,
  downtimeAttachedResources,
  duration,
  endTime,
  fixed,
  id,
  parent,
  startTime,
}): DowntimeParams => ({
  comment,
  duration,
  end_time: formatISO(endTime),
  is_fixed: fixed,
  parent_resource_id: parent?.id || null,
  resource_id: id,
  start_time: formatISO(startTime),
  with_services: downtimeAttachedResources,
});

const setDowntimeOnResources = ({
  resources,
  cancelToken,
}): Promise<Array<AxiosResponse>> => {
  const getResourceParamsForType = (resourceType): Array<DowntimeParams> =>
    resources.filter(({ type }) => type === resourceType).map(toDowntimeParams);

  return axios.all(
    [
      {
        params: getResourceParamsForType('host'),
        endpoint: hostDowntimeEndpoint,
      },
      {
        params: getResourceParamsForType('service'),
        endpoint: serviceDowntimeEndpoint,
      },
    ]
      .filter(({ params }) => params.length > 0)
      .map(({ endpoint, params }) =>
        axios.post(endpoint, params, { cancelToken }),
      ),
  );
};

interface CheckParams {
  parent_resource_id?: number | null;
  resource_id: number;
}

const toCheckParams = ({ id, parent }): CheckParams => ({
  parent_resource_id: parent?.id || null,
  resource_id: id,
});

const checkResources = ({
  resources,
  cancelToken,
}): Promise<Array<AxiosResponse>> => {
  const getResourceParamsForType = (resourceType): Array<CheckParams> =>
    resources.filter(({ type }) => type === resourceType).map(toCheckParams);

  return axios.all(
    [
      {
        params: getResourceParamsForType('host'),
        endpoint: hostCheckEndpoint,
      },
      {
        params: getResourceParamsForType('service'),
        endpoint: serviceCheckEndpoint,
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

export {
  acknowledgeResources,
  setDowntimeOnResources,
  checkResources,
  listResources,
  getData,
  getUser,
};
