import axios, { AxiosRequestConfig } from 'axios';

import { buildResourcesEndpoint } from './endpoint';
import { ResourceListing } from '../models';

const mockEndpoint = 'http://localhost:5000/centreon/api/v2/';

const api = axios.create({
  // baseURL: './api/v2/',
  baseURL: mockEndpoint,
});

const getData = ({ endpoint, requestParams }): Promise<ResourceListing> =>
  api.get(endpoint, requestParams).then(({ data }) => data);

const listResources = (
  endpointParams,
  requestParams: AxiosRequestConfig = {},
): Promise<ResourceListing> =>
  getData({ endpoint: buildResourcesEndpoint(endpointParams), requestParams });

export { listResources };
