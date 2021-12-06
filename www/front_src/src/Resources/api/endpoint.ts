const baseEndpoint = './api/latest';

const monitoringEndpoint = `${baseEndpoint}/monitoring`;
const resourcesEndpoint = `${monitoringEndpoint}/resources`;
const userEndpoint =
  './api/internal.php?object=centreon_topcounter&action=user';

export { baseEndpoint, monitoringEndpoint, resourcesEndpoint, userEndpoint };
