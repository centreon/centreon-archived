import axios from 'axios';

export const create = (url) => {
  return axios.create({
    baseURL: `./api/${url}`,
  });
};

export default create;
