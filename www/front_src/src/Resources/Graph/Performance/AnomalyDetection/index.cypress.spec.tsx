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
import {
  labelCancel,
  labelClose,
  labelDisplayEvents,
  labelGraph,
  labelLast31Days,
  labelLast7Days,
  labelLastDay,
  labelMenageEnvelope,
  labelMenageEnvelopeSubTitle,
  labelUseDefaultValue,
} from '../../../translatedLabels';

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

document.getElementById('cy-root').style = 'min-height:750px;display:flex';

describe('Anomaly detection', () => {
  beforeEach(() => {
    cy.server();
    cy.viewport(1200, 750);
    cy.fixture('resources/resourceListing.json').as('listResource');
    cy.fixture('resources/userFilter.json').as('userFilter');
    cy.fixture('resources/detailsAnomalyDetection.json').as(
      'detailsAnomalyDetection',
    );
    cy.fixture('resources/performanceGraphAnomalyDetection.json').as(
      'graphAnomalyDetection',
    );
    cy.route('GET', '**/resources?*', '@listResource').as('getResourceList');
    cy.route('GET', '**/events-view?*', '@userFilter').as('filter');
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
    cy.display_filter_Menu();

    filtersToBeDisplayedInTypeMenu.map((item) =>
      cy.contains(item).should('be.visible'),
    );

    // cy.matchImageSnapshot();
  });

  it('display the filters of anomaly detection on search bar when  filters of anomaly-detection in filter Menu are checked ', () => {
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
    cy.route('GET', '**/resources?*', '@listResourceByType').as(
      'getResourceListByType',
    );
    cy.display_filter_Menu();

    filtersToBeDisplayedInTypeMenu.map((item) =>
      cy.contains(item).should('be.visible').click(),
    );

    cy.get('[data-testid="Search"]').click();

    cy.click_outside();

    cy.fixture('resources/resourceListingByTypeAnomalyDetection.json').then(
      (data) => {
        data.result.map((item) => cy.contains(item.name).should('be.visible'));
      },
    );
    // cy.matchImageSnapshot();
  });

  it('display the wrench icon on graph actions when one row of resource anomaly-detection is clicked ', () => {
    cy.contains('ad').click();
    cy.get('[data-testid="3"]').contains(labelGraph).click();
    cy.click_outside();
    cy.get('[data-testid="editAnomalyDetectionIcon"]').should('be.visible');

    // cy.matchImageSnapshot();
  });

  it.only('display the modal of edit anomaly-detection when wrench icon is clicked ', () => {
    cy.contains('ad').click();
    cy.get('[data-testid="3"]').click();
    cy.get('[data-testid="editAnomalyDetectionIcon"]').click();

    cy.get('[data-testid="modal_edit_anomaly_detection"]').should('be.visible');
    cy.contains(labelClose).should('be.visible');
    cy.contains(labelLastDay).should('be.visible');
    cy.contains(labelLast7Days).should('be.visible');
    cy.contains(labelLast31Days).should('be.visible');
    cy.contains(labelDisplayEvents).should('be.visible');

    cy.fixture('resources/performanceGraphAnomalyDetection.json').then(
      (data) => {
        cy.contains(data.global.title).should('be.visible');
        data.metrics.map(({ legend }) =>
          cy.contains(legend).should('be.visible'),
        );
      },
    );

    cy.contains(labelMenageEnvelope).should('be.visible');
    cy.contains(labelMenageEnvelopeSubTitle).should('be.visible');
    cy.contains(labelUseDefaultValue).should('be.visible');
    cy.contains(labelCancel).should('be.visible');
    cy.get('[data-testid="save"]').should('be.disabled');

    cy.fixture('resources/detailsAnomalyDetection.json').then((data) => {
      cy.get('[data-testid="add"]')
        .contains(data.sensitivity.maximum_value)
        .should('be.visible');
      cy.get('[data-testid="remove"]')
        .contains(data.sensitivity.minimum_value)
        .should('be.visible');

      cy.get('[data-testid="slider"]')
        .contains(data.sensitivity.default_value)
        .should('be.visible');
      cy.contains('Default').should('be.visible');

      // cy.matchImageSnapshot();
    });
  });
});
