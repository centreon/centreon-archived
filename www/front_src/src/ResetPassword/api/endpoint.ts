import { baseEndpoint } from '../../api/endpoint';

export const resetPasswordEndpoint = (alias: string): string =>
  `${baseEndpoint}/authentication/users/${alias}/password`;
