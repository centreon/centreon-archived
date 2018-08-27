export const serverNameValidator = serverName => !serverName ? 'The field is required' : '';

export const serverIpAddressValidator = serverIpAddress => !serverIpAddress ? 'The field is required' : '';

export const centralIpAddressValidator = centralIpAddress => !centralIpAddress ? 'The field is required' : '';

export const selectRemoteServerValidator = selectRemoteServer => !selectRemoteServer ? 'The field is required' : '';

export const databaseUserValidator = databaseUser => !databaseUser ? 'The field is required' : '';

export const databasePasswordValidator = databasePassword => !databasePassword ? 'The field is required' : '';

export const selectDistantPollersValidator = selectDistantPollers => !selectDistantPollers ? 'The field is required' : '';