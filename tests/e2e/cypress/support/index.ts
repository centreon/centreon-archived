import {
  setUserTokenApi,
  insertResources,
  setFiltersUser,
  delFiltersUser,
} from './centreonData';

before(() => {
  cy.log('-----------------Start-----------------');
  cy.exec('docker cp cypress/fixtures/clapi/ centreon-dev:/tmp/');

  insertResources();

  cy.exec(
    'docker exec centreon-dev centreon -u admin -p centreon -a APPLYCFG -v 1',
  );

  setUserTokenApi();

  cy.exec(`npx wait-on ${Cypress.config().baseUrl}`).then(() => {
    cy.fixture('resources/filters.json').then((filters) => {
      setFiltersUser('POST', filters);
      cy.wait(5000);

      cy.visit(`${Cypress.config().baseUrl}`);

      cy.fixture('users/admin.json').then((userAdmin) => {
        cy.get('input[placeholder="Login"]').type(userAdmin.login);
        cy.get('input[placeholder="Password"]').type(userAdmin.password);
      });

      cy.get('form').submit();
    });
  });
});

beforeEach(() => Cypress.Cookies.preserveOnce('PHPSESSID'));

after(() => {
  delFiltersUser();
  cy.clearLocalStorage();
  cy.log('-----------------End-----------------');
});
