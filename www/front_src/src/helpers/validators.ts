export const serverNameValidator = (serverName: string): string =>
  !serverName ? 'The field is required' : '';

export const serverIpAddressValidator = (serverIpAddress: Array): string => {
  let message = '';
  message =
    !serverIpAddress || serverIpAddress.length < 1
      ? 'The field is required'
      : '';
  return message;
};

export const centralIpAddressValidator = (centralIpAddress: Array): string => {
  let message = '';
  message =
    !centralIpAddress || centralIpAddress.length < 1
      ? 'The field is required'
      : '';
  return message;
};

export const centreonPathValidator = (centreonFolder: Array): string => {
  return !centreonFolder || centreonFolder.length < 1
    ? 'The field is required'
    : '';
};

export const selectRemoteServerValidator = (
  selectRemoteServer: Array,
): string =>
  !selectRemoteServer || selectRemoteServer.length < 1
    ? 'The field is required'
    : '';

export const databaseUserValidator = (databaseUser: string): string =>
  !databaseUser ? 'The field is required' : '';

export const databasePasswordValidator = (databasePassword: string): string =>
  !databasePassword ? 'The field is required' : '';

export const selectDistantPollersValidator = (selectDistantPollers): string =>
  !selectDistantPollers ? 'The field is required' : '';
