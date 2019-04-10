import axios from "axios";
import config from "../config";

export const create = url => {
  return axios.create({
    baseURL: `${config.apiBase}${url}`
  });
};

export default create;
