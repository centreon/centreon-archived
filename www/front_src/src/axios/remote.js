import axios from "axios";
import config from "../config";

export default url => {
    return axios.create({
        baseURL: url
    });
};
