import axios, { AxiosRequestConfig, AxiosResponse, CancelToken } from 'axios';
import formatISO from 'date-fns/formatISO';
import { map, pick } from 'ramda';

import {
  buildResourcesEndpoint,
  serviceCheckEndpoint,
  hostCheckEndpoint,
  userEndpoint,
  acknowledgeEndpoint,
  downtimeEndpoint,
} from './endpoint';
import { ResourceListing, User, Resource } from '../models';

const getData = <TData>({ endpoint, requestParams }): Promise<TData> =>
  axios.get(endpoint, requestParams).then(({ data }) => data);

const listResources = (
  endpointParams,
  requestParams: AxiosRequestConfig = {},
): Promise<ResourceListing> =>
  getData({ endpoint: buildResourcesEndpoint(endpointParams), requestParams });

interface AcknowledgeParams {
  acknowledgeAttachedResources?: boolean;
  notify: boolean;
  comment: string;
}

interface ResourcesWithAcknoweldgeParams {
  resources: Array<Resource>;
  params: AcknowledgeParams;
  cancelToken: CancelToken;
}

const acknowledgeResources = ({
  resources,
  params,
  cancelToken,
}: ResourcesWithAcknoweldgeParams): Promise<Array<AxiosResponse>> => {
  return axios.post(
    acknowledgeEndpoint,
    {
      resources: map(pick(['type', 'id', 'parent']), resources),
      acknowledgement: {
        with_services: params.acknowledgeAttachedResources,
        is_notify_contacts: params.notify,
        comment: params.comment,
      },
    },
    { cancelToken },
  );
};

interface DowntimeParams {
  comment: string;
  duration: number;
  startTime: Date;
  endTime: Date;
  fixed: boolean;
  downtimeAttachedResources?: boolean;
}

interface ResourcesWithDowntimeParams {
  resources: Array<Resource>;
  params: DowntimeParams;
  cancelToken: CancelToken;
}

const setDowntimeOnResources = ({
  resources,
  params,
  cancelToken,
}: ResourcesWithDowntimeParams): Promise<Array<AxiosResponse>> => {
  return axios.post(
    downtimeEndpoint,
    {
      resources: map(pick(['type', 'id', 'parent']), resources),
      downtime: {
        with_services: params.downtimeAttachedResources,
        is_fixed: params.fixed,
        start_time: formatISO(params.startTime),
        end_time: formatISO(params.endTime),
        duration: params.duration,
        comment: params.comment,
      },
    },
    { cancelToken },
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
