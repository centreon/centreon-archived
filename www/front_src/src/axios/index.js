import axios from 'axios';

const create = (url) => {
  return axios.create({
    baseURL: `./api/${url}`,
  });
};

export default create;
