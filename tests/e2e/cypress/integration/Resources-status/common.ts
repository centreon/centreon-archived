import {
  apiBase,
  applyConfigurationViaClapi,
  checkThatConfigurationIsExported,
  checkThatFixtureServicesExistInDatabase,
  loginAsAdminViaApiV2,
  submitResultsViaClapi,
  versionApi,
  logout,
} from '../../commons';
import {
  initializeResourceData,
  removeResourceData,
  setUserTokenApiV1,
} from '../../support/centreonData';

interface Criteria {
  name: string;
  object_type: string | null;
  type: string;
  value: Array<{ id: string; name: string }>;
}

interface Filter {
  criterias: Array<Criteria>;
  name: string;
}

const stateFilterContainer = '[aria-label="State filter"]';
const searchInput = 'input[placeholder="Search"]';
const refreshButton = '[aria-label="Refresh"]';
const resourceMonitoringApi = /.+api\/beta\/monitoring\/resources.?page.+/;

const apiFilterResources = `${apiBase}/${versionApi}/users/filters/events-view`;

const insertResourceFixtures = () => {
  return loginAsAdminViaApiV2()
    .then(initializeResourceData)
    .then(applyConfigurationViaClapi)
    .then(checkThatConfigurationIsExported)
    .then(submitResultsViaClapi)
    .then(checkThatFixtureServicesExistInDatabase)
    .then(() => cy.visit(`${Cypress.config().baseUrl}`))
    .then(() => cy.fixture('users/admin.json'));
};

const setUserFilter = (body: Filter): Cypress.Chainable => {
  return cy
    .request({
      body,
      method: 'POST',
      url: apiFilterResources,
    })
    .then((response) => {
      expect(response.status).to.eq(200);
      customFilterId = response.body.id;
    });
};

const deleteUserFilter = (): Cypress.Chainable => {
  if (customFilterId === null) {
    return cy.wrap({});
  }

  return cy
    .request({
      method: 'DELETE',
      url: `${apiFilterResources}/${customFilterId}`,
    })
    .then((response) => {
      expect(response.status).to.eq(204);
      customFilterId = null;
    });
};

const tearDownResource = () => {
  return setUserTokenApiV1()
    .then(removeResourceData)
    .then(applyConfigurationViaClapi)
    .then(logout);
};

const actionBackgroundColors = {
  acknowledge: 'rgb(247, 244, 229)',
  inDowntime: 'rgb(249, 231, 255)',
};
const actions = {
  acknowledge: 'Acknowledge',
  setDowntime: 'Set downtime',
};

let customFilterId = null;

export {
  stateFilterContainer,
  searchInput,
  refreshButton,
  resourceMonitoringApi,
  actionBackgroundColors,
  actions,
  insertResourceFixtures,
  setUserFilter,
  deleteUserFilter,
  tearDownResource,
};
