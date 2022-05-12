import React from 'react';

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
  it('Initial menu', () => {
    cy.get("[alt='mini logo']").should('be.visible');
    cy.get('li')
      .should('have.length', 2)
      .each(($li) => {
        cy.wrap($li).get('svg').should('be.visible');
      });
  });

  it('menu should expanded after clicking on logo', () => {
    cy.get("[alt='mini logo']").click();
    cy.get("[alt='logo']").should('be.visible');
    cy.get('li')
      .should('have.length', 2)
      .each(($li, index) => {
        cy.wrap($li).as('element').get('svg').should('be.visible');
        if (index === 0) {
          cy.get('@element').contains('Monitoring');
        } else {
          cy.get('@element').contains('Configuration');
        }
      });
  });

  it('collapse should be visible when item is hovered and this item should be colored', () => {
    cy.get("[alt='mini logo']").click();
    cy.get('li').each(($ele) => {
      if ($ele.find('[data-testid=NavigateNextIcon]').length > 0) {
        cy.wrap($ele)
          .find('[data-testid=NavigateNextIcon]')
          .parent()
          .trigger('mouseover');
        cy.get('.MuiCollapse-root').should('be.visible');
      }
    });
  });

  it('item of menu should be colored after double click', () => {
    cy.get('li').first().as('element').trigger('mouseover');
    cy.get('@element').trigger('dblclick');
  });

  it('when item of collapse is clicked , the item and parent item should be colored', () => {
    cy.get("[alt='mini logo']").click();
    cy.get('li')
      .first()
      .each(($ele) => {
        if ($ele.find('[data-testid=NavigateNextIcon]').length > 0) {
          cy.wrap($ele)
            .find('[data-testid=NavigateNextIcon]')
            .parent()
            .trigger('mouseover');
          cy.get('.MuiCollapse-root').as('collapse').should('be.visible');
          cy.get('@collapse')
            .find('.MuiListItemText-root')
            .first()
            .trigger('mouseover')
            .trigger('click');
        }
      });
  });
});
