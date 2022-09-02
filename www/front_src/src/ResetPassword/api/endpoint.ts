import { baseEndpoint } from '../../api/endpoint';

export const getResetPasswordEndpoint = (alias: string): string =>
  `${baseEndpoint}/authentication/users/${alias}/password`;
