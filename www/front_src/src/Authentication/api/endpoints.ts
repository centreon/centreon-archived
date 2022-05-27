import { Provider } from '../models';

const baseEndpoint = './api/latest';

export const authenticationProvidersEndpoint = (provider: Provider): string =>
  `${baseEndpoint}/administration/authentication/providers/${provider}`;
export const contactsEndpoint = `${baseEndpoint}/configuration/users`;
export const contactTemplatesEndpoint = `${baseEndpoint}/configuration/contacts/templates`;
export const contactGroupsEndpoint = `http://localhost:5003/centreon/api/latest/configuration/contacts/groups`;
export const accessGroupsEndpoint = `http://localhost:5003/centreon/api/latest/configuration/access-groups`;
