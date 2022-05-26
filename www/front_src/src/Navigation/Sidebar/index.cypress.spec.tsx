import { BrowserRouter as Router } from 'react-router-dom';
import { renderHook, act } from '@testing-library/react-hooks/dom';
import { useAtom } from 'jotai';

import { mount } from '../../../../../cypress/support';

import { selectedNavigationItemsAtom } from './sideBarAtoms';

import SideBar from './index';

describe('Navigation menu visual testing: ', () => {
  beforeEach(() => {
    cy.fixture('menuData').then((data) => {
      mount(
        <Router>
          <SideBar navigationData={data.result} />
        </Router>,
      );
    });

    const { result } = renderHook(() => useAtom(selectedNavigationItemsAtom));

    act(() => {
      result.current[1](null);
    });
  });

  it('matches the current snapshot "initial menu"', () => {
    cy.get("[alt='mini logo']").should('be.visible');
    cy.get('li').each(($li) => {
      cy.wrap($li).get('svg').should('be.visible');
    });

    cy.matchImageSnapshot();
  });

  it('expands when the logo is clicked', () => {
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

  it('displays the direct child items and highlights the item when hovered ', () => {
    cy.get('li').eq(2).trigger('mouseover');
    cy.get('[data-cy=collapse]').should('be.visible');

    cy.matchImageSnapshot();
  });

  it('hilights the menu item when double clicked', () => {
    cy.get('li').eq(1).as('element').trigger('mouseover');
    cy.get('@element').trigger('dblclick');
    cy.matchImageSnapshot();
  });

  it('hilights the parent item when the item is clicked', () => {
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
