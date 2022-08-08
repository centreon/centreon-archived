import { baseEndpoint } from '../../api/endpoint';

const monitoringEndpoint = `${baseEndpoint}/monitoring`;
const resourcesEndpoint = `${monitoringEndpoint}/resources`;
const userEndpoint =
  './api/internal.php?object=centreon_topcounter&action=user';

export { monitoringEndpoint, resourcesEndpoint, userEndpoint };
