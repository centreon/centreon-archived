import axios, { AxiosRequestConfig } from 'axios';

import { buildResourcesEndpoint } from './endpoint';
import { ResourceListing } from '../models';

const api = axios.create({
  baseURL: './api/beta/',
});

const getData = ({ endpoint, requestParams }): Promise<ResourceListing> =>
  api.get(endpoint, requestParams).then(({ data }) => data);

const listResources = (
  endpointParams,
  requestParams: AxiosRequestConfig = {},
): Promise<ResourceListing> =>
  getData({ endpoint: buildResourcesEndpoint(endpointParams), requestParams });

export { listResources };
