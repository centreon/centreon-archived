const rootUrl = window.location.pathname.split('/')[1];

export default {
  apiBase: process.env.API_BASE ? process.env.API_BASE : "/" + rootUrl + "/api/",
  urlBase: process.env.URL_BASE ? process.env.URL_BASE : "/" + rootUrl + "/"
};
