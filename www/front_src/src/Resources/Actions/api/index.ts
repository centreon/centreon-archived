import formatISO from 'date-fns/formatISO';
import axios, { AxiosResponse, CancelToken } from 'axios';
import { map, pick } from 'ramda';

import {
  acknowledgeEndpoint,
  downtimeEndpoint,
  checkEndpoint,
} from './endpoint';
import { Resource } from '../../models';

interface AcknowledgeParams {
  acknowledgeAttachedResources?: boolean;
  notify: boolean;
  comment: string;
}

interface ResourcesWithAcknowledgeParams {
  resources: Array<Resource>;
  params: AcknowledgeParams;
  cancelToken: CancelToken;
}

const acknowledgeResources = (cancelToken) => ({
  resources,
  params,
}: ResourcesWithAcknowledgeParams): Promise<Array<AxiosResponse>> => {
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

const setDowntimeOnResources = (cancelToken) => ({
  resources,
  params,
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

interface ResourcesWithRequestParams {
  resources: Array<Resource>;
  cancelToken: CancelToken;
}

const checkResources = ({
  resources,
  cancelToken,
}: ResourcesWithRequestParams): Promise<Array<AxiosResponse>> => {
  return axios.post(
    checkEndpoint,
    {
      resources: map(pick(['type', 'id', 'parent']), resources),
    },
    { cancelToken },
  );
};

export { acknowledgeResources, setDowntimeOnResources, checkResources };
