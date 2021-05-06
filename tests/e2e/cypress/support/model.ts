const apiBase = `${Cypress.config().baseUrl}/centreon/api`;

const apiActionV1 = `${apiBase}/index.php`;

const apiFilterResourcesBeta = `${apiBase}/beta/users/filters/events-view`;

const apiLoginV2 = `${apiBase}/v2/login`;
const apiMonitoringBeta = `${apiBase}/beta/monitoring`;
const apiMonitoring = `${apiBase}/v2/monitoring`;

export {
  apiActionV1,
  apiFilterResourcesBeta,
  apiLoginV2,
  apiMonitoringBeta,
  apiMonitoring,
};
