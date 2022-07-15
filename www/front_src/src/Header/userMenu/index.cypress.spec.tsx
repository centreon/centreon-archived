import React from 'react';

import { BrowserRouter as Router } from 'react-router-dom';

import { mount } from '../../../../../cypress/support';
import { centreonUi } from '../helpers';

import UserMenu from './index';

before(() => {
  document.getElementsByTagName('body')[0].style = 'margin:0px';
});

describe('User Menu', () => {
  beforeEach(() => {
    cy.fixture('userMenu').as('user');
    cy.server();
    cy.route('GET', '**/internal.php?**', '@user').as('getUser');

    cy.intercept('PATCH', 'parameters', {
      theme: 'dark',
    }).as('updateTheme');

    cy.stub(centreonUi, 'useLocaleDateTimeFormat').returns({
      format: (): string => 'April 28,2022',
      toTime: (): string => '4:20 PM',
    });

    mount(
      <Router>
        <UserMenu />
      </Router>,
    );
  });

  it('matches the current snapshot "user menu"', () => {
    cy.get('[data-testid=AccountCircleIcon]')
      .as('userIcon')
      .should('be.visible');

    cy.get('[data-cy=clock]').as('clock').should('be.visible');
    cy.get('@clock').contains('April 28,2022');
    cy.get('@clock').contains('4:20 PM');

    cy.matchImageSnapshot();
  });

  it('expands the popper when the user icon is clicked', () => {
    cy.get('[data-testid=AccountCircleIcon]').as('userIcon');
    cy.get('@userIcon').click();
    cy.get('[data-cy=popper]').as('popper').should('be.visible');
    cy.get('@popper').contains('admin');
    cy.get('@popper').contains('Dark');
    cy.get('@popper').contains('Light');
    cy.get('@popper').contains('Logout');
    cy.matchImageSnapshot();
  });

  it('changes style when switch is clicked', () => {
    cy.get('[data-testid=AccountCircleIcon]').click();
    cy.get('[data-cy=themeSwitch]').as('switchMode').should('be.visible');
    cy.get('@switchMode').click();
    cy.matchImageSnapshot();
    cy.get('@switchMode').click();
    cy.matchImageSnapshot();
  });
});
