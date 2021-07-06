const baseEndpoint = './api/v21.10';

const monitoringEndpoint = `${baseEndpoint}/monitoring`;
const resourcesEndpoint = `${monitoringEndpoint}/resources`;
const userEndpoint =
  './api/internal.php?object=centreon_topcounter&action=user';

export { baseEndpoint, monitoringEndpoint, resourcesEndpoint, userEndpoint };
