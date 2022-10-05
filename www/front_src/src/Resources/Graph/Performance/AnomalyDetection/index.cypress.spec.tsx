import React from 'react';

import { act, renderHook } from '@testing-library/react-hooks/dom';
import { Provider, useAtom } from 'jotai';
import { BrowserRouter as Router } from 'react-router-dom';

import { platformVersionsAtom } from '../../../../Main/atoms/platformVersionsAtom';
import { authorizedFilterByModules } from '../../../Filter/Criterias/models';
import { storedFilterAtom } from '../../../Filter/filterAtoms';
import { allFilter } from '../../../Filter/models';
import Resources from '../../../index';
import { enabledAutorefreshAtom } from '../../../Listing/listingAtoms';
import { labelGraph } from '../../../translatedLabels';

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
    cy.server();
    cy.fixture('resources/resourceListing.json').as('listResource');
    cy.fixture('resources/userFilter.json').as('userFilter');
    cy.fixture('resources/detailsAnomalyDetection.json').as(
      'detailsAnomalyDetection',
    );
    cy.fixture('resources/performanceGraphAnomalyDetection.json').as(
      'graphAnomalyDetection',
    );

    cy.route('GET', '**/resources?*', '@listResource').as('getResourceList');
    cy.route('GET', '**/users/filters/events-view?*', '@userFilter');
    cy.route(
      'GET',
      '**/resources/anomaly-detection/1',
      '@detailsAnomalyDetection',
    );
    cy.route('GET', '**/performance?*', '@graphAnomalyDetection').as(
      'getGraphDataAnomalyDetection',
    );

    const storedFilter = renderHook(() => useAtom(storedFilterAtom));

    act(() => {
      storedFilter.result.current[1](allFilter);
    });

    cy.mount(
      <Provider
        initialValues={[
          [platformVersionsAtom, installedModules],
          [enabledAutorefreshAtom, false],
        ]}
      >
        <Router>
          <Resources />
        </Router>
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

  it('display the filters of anomaly detection on search bar when  filters of anomaly-detection in filter Menu are checked ', () => {
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

  it('display resource of type anomaly-detection when  filters of anomaly detection are checked and search button is clicked', () => {
    cy.fixture('resources/resourceListingByTypeAnomalyDetection.json').as(
      'listResourceByType',
    );

    cy.server();
    cy.route('GET', '**/resources?*', '@listResourceByType').as(
      'getResourceListByType',
    );

    cy.display_filter_Menu();

    filtersToBeDisplayedInTypeMenu.map((item) =>
      cy.contains(item).should('be.visible').click(),
    );

    cy.get('[data-testid="Search"]').click();

    const payload = Object.keys(authorizedFilterByModules[moduleName]);

    cy.wait('@getResourceListByType').then((data) => {
      expect(data.url.includes(`&types=${JSON.stringify(payload)}`)).to.equal(
        true,
      );
    });

    cy.click_outside();

    cy.fixture('resources/resourceListingByTypeAnomalyDetection.json').then(
      (data) => {
        data.result.map((item) => cy.contains(item.name).should('be.visible'));
      },
    );
    // cy.matchImageSnapshot();
  });

  it.only('display the wrench icon on graph actions when one row of resource anomaly-detection is clicked ', () => {
    cy.viewport(1200, 750);

    cy.contains('ad').click();
    cy.get('[data-testid="3"]').contains(labelGraph).click();
    cy.click_outside();
    cy.wait('@getGraphDataAnomalyDetection');
    cy.get('[data-testid="editAnomalyDetectionIcon"]').should('be.visible');
    // cy.matchImageSnapshot();
  });

  it.only('display the modal of edit anomaly-detection when wrench icon is clicked ', () => {
    // cy.matchImageSnapshot();
  });
});
