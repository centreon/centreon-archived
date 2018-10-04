import axios from "axios";

export default url => {
    return axios.create({
        baseURL: url
    });
};
