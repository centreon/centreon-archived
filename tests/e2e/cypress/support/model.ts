import { apiBase, versionApi } from '../commons';

const apiLoginV2 = '/centreon/authentication/providers/configurations/local';
const apiMonitoringBeta = `${apiBase}/beta/monitoring`;
const apiMonitoring = `${apiBase}/${versionApi}/monitoring`;
const apiLogout = '/centreon/api/latest/authentication/logout';

export { apiLoginV2, apiMonitoringBeta, apiMonitoring, apiLogout };
