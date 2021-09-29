import { baseEndpoint } from '../../../../../Resources/api/endpoint';

export const monitoringServersEndpoint = `${baseEndpoint}/configuration/monitoring-servers`;
export const exportAndReloadConfigurationEndpoint = (
  pollerId: number,
): string =>
  `http://localhost:5001/centreon/${baseEndpoint}/configuration/pollers/${pollerId}/generate_and_reload`;
