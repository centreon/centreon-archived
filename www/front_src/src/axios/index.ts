import axios from 'axios';

export const create = (url: string) => {
  return axios.create({
    baseURL: `./api/${url}`,
  });
};

export default create;
