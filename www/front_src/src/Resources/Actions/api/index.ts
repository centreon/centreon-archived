import axios, { AxiosResponse, CancelToken } from 'axios';
import { map, pick } from 'ramda';

import { Resource } from '../../models';

import {
  acknowledgeEndpoint,
  downtimeEndpoint,
  checkEndpoint,
  commentEndpoint,
} from './endpoint';

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

const acknowledgeResources = (cancelToken: CancelToken) => ({
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
}

const setDowntimeOnResources = (cancelToken: CancelToken) => ({
  resources,
  params,
}: ResourcesWithDowntimeParams): Promise<AxiosResponse> => {
  return axios.post(
    downtimeEndpoint,
    {
      resources: map(pick(['type', 'id', 'parent']), resources),
      downtime: {
        with_services: params.downtimeAttachedResources,
        is_fixed: params.fixed,
        start_time: params.startTime,
        end_time: params.endTime,
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
}: ResourcesWithRequestParams): Promise<AxiosResponse> => {
  return axios.post(
    checkEndpoint,
    {
      resources: map(pick(['type', 'id', 'parent']), resources),
    },
    { cancelToken },
  );
};

export interface CommentParameters {
  date: string;
  comment: string;
}

interface ResourcesWithCommentParams {
  resources: Array<Resource>;
  parameters: CommentParameters;
}

const commentResources = (cancelToken: CancelToken) => ({
  resources,
  parameters,
}: ResourcesWithCommentParams): Promise<AxiosResponse> => {
  return axios.post(
    commentEndpoint,
    {
      resources: resources.map((resource) => ({
        ...pick(['id', 'type', 'parent'], resource),
        date: parameters.date,
        comment: parameters.comment,
      })),
    },
    { cancelToken },
  );
};

export {
  acknowledgeResources,
  setDowntimeOnResources,
  checkResources,
  commentResources,
};
