import axios, { AxiosResponse, CancelToken } from 'axios';
import { map, pick } from 'ramda';

import { Resource } from '../../models';
import { AcknowledgeFormValues } from '../Resource/Acknowledge';

import {
  acknowledgeEndpoint,
  downtimeEndpoint,
  checkEndpoint,
  commentEndpoint,
} from './endpoint';

interface DowntimeParams {
  comment: string;
  downtimeAttachedResources?: boolean;
  duration: number;
  endTime: Date;
  fixed: boolean;
  startTime: Date;
}
interface ResourcesWithAcknowledgeParams {
  cancelToken: CancelToken;
  params: AcknowledgeFormValues;
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
          force_active_checks: params.forceActiveChecks,
          is_notify_contacts: params.notify,
          is_persistent_comment: params.persistent,
          is_sticky: params.isSticky,
          with_services: params.acknowledgeAttachedResources,
        },
        resources: map(pick(['type', 'id', 'parent']), resources),
      },
      { cancelToken },
    );
  };

interface ResourcesWithDowntimeParams {
  params: DowntimeParams;
  resources: Array<Resource>;
}

const setDowntimeOnResources =
  (cancelToken: CancelToken) =>
  ({
    resources,
    params,
  }: ResourcesWithDowntimeParams): Promise<AxiosResponse> => {
    return axios.post(
      downtimeEndpoint,
      {
        downtime: {
          comment: params.comment,
          duration: params.duration,
          end_time: params.endTime,
          is_fixed: params.fixed,
          start_time: params.startTime,
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
  comment: string;
  date: string;
}

interface ResourcesWithCommentParams {
  parameters: CommentParameters;
  resources: Array<Resource>;
}

const commentResources =
  (cancelToken: CancelToken) =>
  ({
    resources,
    parameters,
  }: ResourcesWithCommentParams): Promise<AxiosResponse> => {
    return axios.post(
      commentEndpoint,
      {
        resources: resources.map((resource) => ({
          ...pick(['id', 'type', 'parent'], resource),
          comment: parameters.comment,
          date: parameters.date,
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
