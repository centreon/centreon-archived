import * as path from 'path';

const apiBase = `${Cypress.config().baseUrl}/centreon/api`;

const apiLogin = `${apiBase}/v2/login`;
const apiFilterResources = `${apiBase}/beta/users/filters/events-view`;

const apiMonitoring = `${apiBase}/v2/monitoring`;

const clapiFixturesPath = path.resolve('/tmp');

export { apiFilterResources, apiLogin, apiMonitoring, clapiFixturesPath };
