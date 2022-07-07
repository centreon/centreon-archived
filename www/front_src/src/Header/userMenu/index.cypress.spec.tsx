import { BrowserRouter as Router } from 'react-router-dom';

import { mount } from '../../../../../cypress/support';
import Header from '../index';

import { userEndpoint } from './api/endpoint';

import UserMenu from './index';

beforeEach(() => {
  // cy.request(
  //   'http://localhost:9090/__cypress/iframes//home/vagrant/centreon-dev-ecosystem/apps/centreon/www/front_src/src/Header/userMenu/api/internal.php?object=centreon_topcounter&action=user',
  // ).as('todos');

  cy.intercept(
    'http://localhost:9090/__cypress/iframes//home/vagrant/centreon-dev-ecosystem/apps/centreon/www/front_src/src/Header/userMenu/api/latest/configuration/users/current/parameters',
    {
      theme: 'testoooo',
    },
  ).as('updateTheme');
});

describe('User Menu', () => {
  it('matches the current snapshot "initial menu"', () => {
    mount(
      <Router>
        <div style={{ background: 'black', width: '100%' }}>
          <UserMenu />
        </div>
      </Router>,
    );

    cy.request({
      failOnStatusCode: false,
      method: 'GET',
      url: 'http://localhost:9090/__cypress/iframes//home/vagrant/centreon-dev-ecosystem/apps/centreon/www/front_src/src/Header/userMenu/api/internal.php?object=centreon_topcounter&action=user',
    }).as('todos');

    // cy.wait('@todos').then((res) => cy.log('reeeeeeeeeees', res));

    // const matching = Cypress.minimatch(
    //   'parameters',
    //   'http://localhost:9090/__cypress/iframes//home/vagrant/centreon-dev-ecosystem/apps/centreon/www/front_src/src/Header/userMenu/api/latest/configuration/users/current/parameters',
    //   {
    //     matchBase: true,
    //   },
    // );

    const matching = Cypress.minimatch(
      'http://localhost:9090/__cypress/iframes//home/vagrant/centreon-dev-ecosystem/apps/centreon/www/front_src/src/Header/userMenu/api/internal.php?object=centreon_topcounter&action=user',
      'http://localhost:9090/__cypress/iframes//home/vagrant/centreon-dev-ecosystem/apps/centreon/www/front_src/src/Header/userMenu/api/internal.php?**',

      {
        matchBase: true,
      },
    );

    cy.log('matching', matching);

    cy.get('[data-testid=AccountCircleIcon]')
      .as('userIcon')
      .should('be.visible');

    cy.get('@userIcon').click();

    // cy.wait('@getBuildings').then((res) => cy.log('reeeeeeeeeees', res));

    //   .its('response')
    //   .should('deep.include', {
    //     statusCode: 200,
    //     statusMessage: 'OK',
    //   })
    //   .and('have.property', 'body') // yields the "response.body"
    //   .then((body) => {
    //     cy.log('body', body);
    //     // since we do not know the number of items
    //     // just check if it is an array
    //   });

    cy.get('[data-cy=popper]').as('popper').should('be.visible');
    cy.get('@popper').contains('Dark');
    cy.get('@popper').contains('Light');
    cy.get('@popper').contains('Logout');

    // cy.matchImageSnapshot();
  });

  it('switch theme mode', () => {
    // cy.matchImageSnapshot();

    mount(
      <Router>
        <div style={{ background: 'black', width: '100%' }}>
          <UserMenu />
        </div>
      </Router>,
    );

    cy.get('[data-testid=AccountCircleIcon]').click();

    cy.get('[data-cy=switch]').as('switchMode').should('be.visible');

    cy.get('@switchMode').click();

    cy.wait('@updateTheme').then((res) => cy.log('reeeeeeeeeees', res));

    // cy.matchImageSnapshot();
  });
});
