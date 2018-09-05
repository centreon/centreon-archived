import axios from "axios";
import config from "../config";

export default (url) => {
    return axios.create({
        baseURL: `${config.apiBase}${url}`,
    })
}