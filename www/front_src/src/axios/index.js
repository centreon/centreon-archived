import axios from "axios";

export const create = url => {
  return axios.create({
    baseURL: `http://10.30.2.72/centreon/api/${url}`
  });
};

export default create;
