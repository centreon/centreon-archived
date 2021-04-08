import axios, { AxiosInstance } from 'axios';

const create = (url: string): AxiosInstance => {
  return axios.create({
    baseURL: `./api/${url}`,
  });
};

export default create;
