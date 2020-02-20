import axios from 'axios';
import { resourceEndpoint } from './endpoint';
import { Listing } from '../models';

const mockEndpoint = 'http://localhost:5000/centreon/api/v2/';

const api = axios.create({
  // baseURL: './api/v2/',
  baseURL: mockEndpoint,
});

const getData = (endpoint) => api.get(endpoint).then(({ data }) => data);

const listResources = (): Promise<Listing> => getData(resourceEndpoint);

export { listResources };
