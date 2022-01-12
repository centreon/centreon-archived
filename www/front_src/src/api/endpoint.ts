export const baseEndpoint = './api/latest';
const mockEndpoint = 'http://localhost:5003/centreon/api/latest';

export const webVersionsEndpoint = `${mockEndpoint}/platform/versions/web`;
export const userEndpoint = `${baseEndpoint}/configuration/users/current/parameters`;
export const logoutEndpoint = `${baseEndpoint}/authentication/logout`;
