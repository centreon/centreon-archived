import { Provider } from '../models';

const baseEndpoint = './api/latest';

export const authenticationProvidersEndpoint = (provider: Provider): string =>
  `http://localhost:5004/centreon/api/latest/administration/authentication/providers/openid`;
export const contactsEndpoint = `${baseEndpoint}/configuration/users`;
export const contactTemplatesEndpoint = `${baseEndpoint}/configuration/contacts/templates`;
export const contactGroupsEndpoint = `${baseEndpoint}/configuration/contacts/groups`;
export const accessGroupsEndpoint = `${baseEndpoint}/configuration/access-groups`;
