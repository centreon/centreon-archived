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
  comment: string;
  notify: boolean;
}

interface ResourcesWithAcknowledgeParams {
  cancelToken: CancelToken;
  params: AcknowledgeParams;
  resources: Array<Resource>;
}

const acknowledgeResources =
  (cancelToken: CancelToken) =>
  ({
    resources,
    params,
  }: ResourcesWithAcknowledgeParams): Promise<Array<AxiosResponse>> => {
    return axios.post(
      acknowledgeEndpoint,
      {
        acknowledgement: {
          comment: params.comment,
          is_notify_contacts: params.notify,
          with_services: params.acknowledgeAttachedResources,
        },
        resources: map(pick(['type', 'id', 'parent']), resources),
      },
      { cancelToken },
    );
  };

interface DowntimeParams {
  comment: string;
  downtimeAttachedResources?: boolean;
  duration: number;
  endTime: Date;
  fixed: boolean;
  startTime: Date;
}

interface ResourcesWithDowntimeParams {
  cancelToken: CancelToken;
  params: DowntimeParams;
  resources: Array<Resource>;
}

const setDowntimeOnResources =
  (cancelToken: CancelToken) =>
  ({
    resources,
    params,
  }: ResourcesWithDowntimeParams): Promise<Array<AxiosResponse>> => {
    return axios.post(
      downtimeEndpoint,
      {
        downtime: {
          comment: params.comment,
          duration: params.duration,
          end_time: formatISO(params.endTime),
          is_fixed: params.fixed,
          start_time: formatISO(params.startTime),
          with_services: params.downtimeAttachedResources,
        },
        resources: map(pick(['type', 'id', 'parent']), resources),
      },
      { cancelToken },
    );
  };

interface ResourcesWithRequestParams {
  cancelToken: CancelToken;
  resources: Array<Resource>;
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
