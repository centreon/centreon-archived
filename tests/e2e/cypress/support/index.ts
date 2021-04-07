import {
  setUserTokenApi,
  insertResources,
  setFiltersUser,
  delFiltersUser,
  checkServiceApi,
} from './centreonData';

before(() => {
  cy.log('-----------------Start-----------------');
  cy.exec('docker cp cypress/fixtures/clapi/ centreon-dev:/tmp/');

  insertResources();

  cy.exec(
    'docker exec centreon-dev centreon -u admin -p centreon -a APPLYCFG -v 1',
  );

  setUserTokenApi();
  // checkServiceApi();

  cy.exec(`npx wait-on ${Cypress.config().baseUrl}`).then(() => {
    cy.fixture('resources/filters.json').then((filters) => {
      setFiltersUser('POST', filters);

      // failOnStatusCode it's FALSE to ignore the first 404 on Centreon redirection
      cy.visit(`${Cypress.config().baseUrl}`, { failOnStatusCode: false });

      cy.fixture('users/admin.json').then((userAdmin) => {
        cy.get('input[placeholder="Login"]').type(userAdmin.login);
        cy.get('input[placeholder="Password"]').type(userAdmin.password);
      });

      cy.get('form').submit();
    });
  });
});

beforeEach(() =>
  Cypress.Cookies.defaults({
    preserve: 'PHPSESSID',
  }),
);

after(() => {
  // delFiltersUser();
  cy.log('-----------------End-----------------');
});
