const apiLogin = `${Cypress.config().baseUrl}/centreon/api/v2/login`;

const apiFilterResources = `${
  Cypress.config().baseUrl
}/centreon/api/beta/users/filters/events-view`;

export { apiFilterResources, apiLogin };
