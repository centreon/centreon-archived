import React from 'react';

import { Provider } from 'jotai';

import { authorizedFilterByModules } from '../../../Filter/Criterias/models';
import { platformVersionsAtom } from '../../../../Main/atoms/platformVersionsAtom';
import { enabledAutorefreshAtom } from '../../../Listing/listingAtoms';
import Filter from '../../../Filter/index';
import Listing from '../../../Listing/index';

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

const filtersToBeDisplayedInTypeMenu = Object.values(
  authorizedFilterByModules[moduleName],
);

const filtersToBeDisplayedInSearchBar = Object.keys(
  authorizedFilterByModules[moduleName],
);

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

describe('Anomaly detection', () => {
  beforeEach(() => {
    cy.fixture('resources/resourceListing.json').as('listResource');

    cy.server();
    cy.route('GET', '**/resources?*', '@listResource').as('getResourceList');

    cy.mount(
      <Provider
        initialValues={[
          [platformVersionsAtom, installedModules],
          [enabledAutorefreshAtom, false],
        ]}
      >
        <Filter />
        <Listing />
      </Provider>,
    );
  });

  it('display the filters of anomaly-detection in filter Menu when the module centreon-anomaly-detection is installed', () => {
    cy.viewport(750, 750);

    cy.display_filter_Menu();

    filtersToBeDisplayedInTypeMenu.map((item) =>
      cy.contains(item).should('be.visible'),
    );

    // cy.matchImageSnapshot();
  });

  it('display the filters of anomaly detection on search bar when user checks filters of anomaly-detection in filter Menu', () => {
    cy.viewport(750, 750);

    cy.display_filter_Menu();

    filtersToBeDisplayedInTypeMenu.map((item) => {
      cy.contains(item).should('be.visible').click();
      cy.get('input[type="checkbox"]').should('be.checked');

      return null;
    });

    cy.get('[data-testid="searchBar"]')
      .find('input')
      .should('have.value', `type:${searchWords.type} `);

    // cy.matchImageSnapshot();
  });

  it.only('display resource of type anomaly-detection when user checks filter anomaly detection and clicked on search button', () => {
    cy.fixture('resources/resourceListingByTypeAnomalyDetection.json').as(
      'listResourceByType',
    );
    cy.server();
    cy.route('GET', '**/resources?*', '@listResourceByType').as(
      'getResourceListByType',
    );
    cy.viewport(750, 750);

    cy.display_filter_Menu();

    filtersToBeDisplayedInTypeMenu.map((item) => {
      cy.contains(item).should('be.visible').click();
      cy.get('input[type="checkbox"]').should('be.checked');

      return null;
    });

    cy.get('[data-testid="Search"]').click();

    const payload = Object.keys(authorizedFilterByModules[moduleName]);

    cy.wait('@getResourceListByType').then(({ url }) => {
      expect(url.includes(`&types=${JSON.stringify(payload)}`)).to.equal(true);
    });
  });
});
