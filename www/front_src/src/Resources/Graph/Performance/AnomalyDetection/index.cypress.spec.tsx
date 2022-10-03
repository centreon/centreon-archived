import React from 'react';

import { Provider } from 'jotai';

import { authorizedFilterByModules } from '../../../Filter/Criterias/models';
import { platformVersionsAtom } from '../../../../Main/atoms/platformVersionsAtom';
import Filter from '../../../Filter/index';

// import Resources from '../../../index';

const installedModules = {
  modules: {
    'centreon-anomaly-detection': {
      fix: '0-beta',
      major: '22',
      minor: '10',
      version: '22.10.0-beta.1',
    },
  },
  web: {},
  widgets: [],
};

const moduleName = 'centreon-anomaly-detection';

interface Search {
  type: string;
}

describe('Anomaly detection', () => {
  cy.viewport(750, 750);

  beforeEach(() => {
    cy.mount(
      <Provider initialValues={[[platformVersionsAtom, installedModules]]}>
        <Filter />,
      </Provider>,
    );
  });

  it('display the filters of anomaly-detection in filter menu and search bar when the module centreon-anomaly-detection is installed', () => {
    const filtersToBeDisplayedInTypeMenu = Object.values(
      authorizedFilterByModules[moduleName],
    );

    const filtersToBeDisplayedInSearchBar = Object.keys(
      authorizedFilterByModules[moduleName],
    );

    cy.get('[aria-label="Filter options"]').click();
    cy.contains('Type').should('be.visible').click();

    filtersToBeDisplayedInTypeMenu.map((item) => {
      cy.contains(item).should('be.visible').click();
      cy.get('input[type="checkbox"]').should('be.checked');

      return null;
    });

    const searchWords = filtersToBeDisplayedInSearchBar.reduce(
      (prevValue, currentValue: string): Search => {
        const value = prevValue.type;

        const searchKeyWords = {
          type: value ? `${value},${currentValue}` : `${currentValue}`,
        };

        return { ...prevValue, ...searchKeyWords };
      },
      { type: '' },
    );

    cy.get('[data-testid="search"]')
      .find('input')
      .should('have.value', `type:${searchWords.type} `);
  });
});
