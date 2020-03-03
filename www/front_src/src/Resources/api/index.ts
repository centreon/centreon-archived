import axios, { AxiosRequestConfig } from 'axios';

import { buildResourcesEndpoint } from './endpoint';
import { ResourceListing } from '../models';

const api = axios.create({
  baseURL: 'http://localhost:5000/api/beta/',
});

const getData = <TData>({ endpoint, requestParams }): Promise<TData> =>
  api.get(endpoint, requestParams).then(({ data }) => data);

const listResources = (
  endpointParams,
  requestParams: AxiosRequestConfig = {},
): Promise<ResourceListing> =>
  getData({ endpoint: buildResourcesEndpoint(endpointParams), requestParams });

export { listResources, getData };
