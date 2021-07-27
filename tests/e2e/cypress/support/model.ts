const apiBase = `${Cypress.config().baseUrl}/centreon/api`;

const apiActionV1 = `${apiBase}/index.php`;

const apiFilterResourcesBeta = `${apiBase}/beta/users/filters/events-view`;

const versionApi = 'latest';
const apiLoginV2 = `${apiBase}/${versionApi}/login`;
const apiMonitoringBeta = `${apiBase}/beta/monitoring`;
const apiMonitoring = `${apiBase}/${versionApi}/monitoring`;

export {
  apiActionV1,
  apiFilterResourcesBeta,
  apiLoginV2,
  apiMonitoringBeta,
  apiMonitoring,
};
