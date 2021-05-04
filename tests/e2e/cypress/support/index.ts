import { clapiFixturesPath } from './model';
import { insertResources } from './centreonData';

before(() => {
  cy.exec(
    `docker cp cypress/fixtures/clapi/resources.txt ${Cypress.env(
      'dockerName',
    )}:${clapiFixturesPath}/resources.txt`,
  );

  insertResources();

  cy.exec(
    `docker exec ${Cypress.env(
      'dockerName',
    )} centreon -u admin -p centreon -a APPLYCFG -v 1`,
  );

  cy.exec(`npx wait-on ${Cypress.config().baseUrl}`).then(() => {
    // failOnStatusCode it's FALSE to ignore the first 404 on Centreon redirection
    cy.visit(`${Cypress.config().baseUrl}`, { failOnStatusCode: false });

    cy.fixture('users/admin.json').then((userAdmin) => {
      cy.get('input[placeholder="Login"]').type(userAdmin.login);
      cy.get('input[placeholder="Password"]').type(userAdmin.password);
    });

    cy.get('form').submit();
  });

  Cypress.Cookies.defaults({
    preserve: 'PHPSESSID',
  });
});
