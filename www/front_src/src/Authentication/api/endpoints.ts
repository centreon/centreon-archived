import { Provider } from '../models';

const baseEndpoint = './api/latest';

export const authenticationProvidersEndpoint = (provider: Provider): string =>
  `${baseEndpoint}/api/latest/administration/authentication/providers/${provider}`;
export const contactsEndpoint = `${baseEndpoint}/configuration/users`;
