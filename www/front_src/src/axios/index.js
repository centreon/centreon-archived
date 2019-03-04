import axios from "axios";
import config from "../config";

export const CentreonAxios = url => {
  return axios.create({
    baseURL: `${config.apiBase}${url}`
  });
};

export default CentreonAxios;