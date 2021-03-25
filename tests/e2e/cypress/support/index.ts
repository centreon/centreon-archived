import { insertResources, setFiltersUser } from './centreonData';
import { apiLogin } from './model';

const setUserTokenApi = () => {
  cy.fixture('users/admin.json').then((userAdmin) => {
    cy.request({
      method: 'POST',
      url: apiLogin,
      body: {
        security: {
          credentials: {
            login: userAdmin.login,
            password: userAdmin.password,
          },
        },
      },
    }).then(({ body }) =>
      window.localStorage.setItem('userTokenApi', body.security.token),
    );
  });
};

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

after(() => cy.log('-----------------End-----------------'));
