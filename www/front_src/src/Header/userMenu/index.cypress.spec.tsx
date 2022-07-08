import { BrowserRouter as Router } from 'react-router-dom';

import { mount } from '../../../../../cypress/support';

import UserMenu from './index';

describe('User Menu', () => {
  beforeEach(() => {
    cy.fixture('userMenu').as('user');
    cy.server();
    cy.route('GET', '**/internal.php?**', '@user').as('todos');

    cy.intercept('PATCH', 'parameters', {
      theme: 'dark',
    }).as('updateTheme');

    mount(
      <Router>
        <div style={{ background: 'black', width: '100%' }}>
          <UserMenu />
        </div>
      </Router>,
    );
  });
  it.only('matches the current snapshot "initial menu"', () => {
    cy.get('[data-testid=AccountCircleIcon]')
      .as('userIcon')
      .should('be.visible');

    cy.get('@userIcon').click().wait('@todos');

    cy.get('[data-cy=popper]').as('popper').should('be.visible');
    cy.get('@popper').contains('Dark');
    cy.get('@popper').contains('Light');
    cy.get('@popper').contains('Logout');

    // cy.matchImageSnapshot();
  });

  it('switch theme mode', () => {
    // cy.matchImageSnapshot();

    cy.get('[data-testid=AccountCircleIcon]').click();

    cy.get('[data-cy=switch]').as('switchMode').should('be.visible');

    cy.get('@switchMode').click();

    cy.log('url', cy.url());

    // cy.wait('@updateTheme').then((res) => cy.log('reeeeeeeeeees', res));

    // cy.matchImageSnapshot();
  });
});
