import axios, { AxiosRequestConfig } from 'axios';

import { buildResourcesEndpoint } from './endpoint';
import { ResourceListing } from '../models';

const getData = <TData>({ endpoint, requestParams }): Promise<TData> =>
  axios.get(endpoint, requestParams).then(({ data }) => data);

const listResources = (
  endpointParams,
  requestParams: AxiosRequestConfig = {},
): Promise<ResourceListing> =>
  getData({ endpoint: buildResourcesEndpoint(endpointParams), requestParams });

export { listResources, getData };
