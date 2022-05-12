import { BrowserRouter as Router } from 'react-router-dom';

import { mount } from '../../../../../cypress/support';

import SideBar from './index';

describe('Visual testing for menu: ', () => {
  beforeEach(() => {
    cy.fixture('dataMenu').then((data) => {
      mount(
        <Router>
          <SideBar navigationData={data.result} />
        </Router>,
      );
    });
  });
  it('Should match previous screenshot "initial menu"', () => {
    cy.get("[alt='mini logo']").should('be.visible');
    cy.get('li').each(($li) => {
      cy.wrap($li).get('svg').should('be.visible');
    });
    cy.matchImageSnapshot();
  });

  it('menu should expanded after clicking on logo', () => {
    cy.get("[alt='mini logo']").click();
    cy.get("[alt='logo']").should('be.visible');
    cy.get('li').each(($li, index) => {
      cy.wrap($li).as('element').get('svg').should('be.visible');
      if (index === 0) {
        cy.get('@element').contains('Monitoring');
      } else if (index === 1) {
        cy.get('@element').contains('Home');
      } else {
        cy.get('@element').contains('Configuration');
      }
    });
    cy.matchImageSnapshot();
  });

  it('collapse should be visible when item of menu is hovered, and it should be colored', () => {
    cy.get('li').eq(2).trigger('mouseover');
    cy.get('[data-cy=collapse]').should('be.visible');
    cy.matchImageSnapshot();
  });

  it('item of menu should be colored after double click', () => {
    cy.get('li').eq(1).as('element').trigger('mouseover');
    cy.get('@element').trigger('dblclick');
    cy.matchImageSnapshot();
  });

  it('when item of collapse is clicked , the item and parent item should be colored', () => {
    cy.get("[alt='mini logo']").click();
    cy.get('li').eq(2).trigger('mouseover');
    cy.get('[data-testid=ExpandMoreIcon]').should('be.visible');
    cy.get('[data-cy=collapse]').as('collapse').should('be.visible');
    cy.get('@collapse')
      .find('ul')
      .first()
      .as('first_ele_collapse')
      .trigger('mouseover');
    cy.get('@first_ele_collapse')
      .find('[data-testid=ExpandMoreIcon]')
      .should('be.visible');
    cy.get('@first_ele_collapse')
      .find('[data-cy=collapse]')
      .as('second_collapse')
      .should('be.visible');
    cy.get('@second_collapse')
      .find('ul')
      .first()
      .trigger('mouseover')
      .trigger('click');
    cy.matchImageSnapshot();
  });
});
