export const serverNameValidator = (serverName) =>
  !serverName ? 'The field is required' : '';

export const serverIpAddressValidator = (serverIpAddress) => {
  let message = '';
  message =
    !serverIpAddress || serverIpAddress.length < 1
      ? 'The field is required'
      : '';
  return message;
};

export const centralIpAddressValidator = (centralIpAddress) => {
  let message = '';
  message =
    !centralIpAddress || centralIpAddress.length < 1
      ? 'The field is required'
      : '';
  return message;
};

export const centreonPathValidator = (centreonFolder) => {
  return !centreonFolder || centreonFolder.length < 1
    ? 'The field is required'
    : '';
};

export const selectRemoteServerValidator = (selectRemoteServer) =>
  !selectRemoteServer || selectRemoteServer.length < 1
    ? 'The field is required'
    : '';

export const databaseUserValidator = (databaseUser) =>
  !databaseUser ? 'The field is required' : '';

export const databasePasswordValidator = (databasePassword) =>
  !databasePassword ? 'The field is required' : '';

export const selectDistantPollersValidator = (selectDistantPollers) =>
  !selectDistantPollers ? 'The field is required' : '';
