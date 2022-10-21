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
  labelEditAnomalyDetectionConfirmation,
  labelGraph,
  labelLast31Days,
  labelLast7Days,
  labelLastDay,
  labelMenageEnvelope,
  labelMenageEnvelopeSubTitle,
  labelPerformanceGraphAD,
  labelUseDefaultValue,
} from '../../../translatedLabels';
import ExportablePerformanceGraphWithTimeline from '../ExportableGraphWithTimeline';
import Filter from '../../../Filter';
import { detailsAtom } from '../../../Details/detailsAtoms';

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

describe('Anomaly detection - Filter', () => {
  beforeEach(() => {
    cy.viewport(1200, 750);

    const storedFilter = renderHook(() => useAtom(storedFilterAtom));

    act(() => {
      storedFilter.result.current[1](allFilter);
    });

    cy.mount(
      <Provider initialValues={[[platformVersionsAtom, installedModules]]}>
        <Filter />
      </Provider>,
    );
  });

  it('displays the Anomaly detection criteria value when the type criteria chip is clicked and centreon-anomaly-detection is installed', () => {
    cy.displayFilterMenu();

    filtersToBeDisplayedInTypeMenu.map((item) =>
      cy.contains(item).should('be.visible'),
    );
    cy.clickOutside();
  });

  it('displays the Anomaly detection criteria value in the search bar when the corresponding type criteria is selected', () => {
    cy.displayFilterMenu();

    filtersToBeDisplayedInTypeMenu.map((item) => {
      cy.contains(item).should('be.visible').click();
      cy.get('input[type="checkbox"]').should('be.checked');

      return null;
    });

    cy.get('[data-testid="searchBar"]')
      .find('input')
      .should('have.value', `type:${searchWords.type} `);
  });

  it('displays the Anomaly detection criteria value on search proposition when user types type: in the search bar', () => {
    cy.get('input[placeholder=Search]').type('type:');
    filtersToBeDisplayedInSearchBar.map((item) =>
      cy.contains(item).should('be.visible'),
    );
  });
});

describe('Anomaly detection - Graph', () => {
  beforeEach(() => {
    cy.viewport(1200, 750);

    cy.fixture('resources/performanceGraphAnomalyDetection.json').as(
      'graphAnomalyDetection',
    );
    cy.server();

    cy.route('GET', '**/performance?*', '@graphAnomalyDetection').as(
      'getGraphDataAnomalyDetection',
    );

    cy.fixture('resources/detailsAnomalyDetection.json').then((data) => {
      cy.mount(
        <Provider initialValues={[[detailsAtom, data]]}>
          <Router>
            <ExportablePerformanceGraphWithTimeline
              interactWithGraph
              graphHeight={280}
              resource={data}
              onReload={(): boolean => false}
            />
          </Router>
        </Provider>,
      );
    });
  });

  it('displays the wrench icon on graph actions when resource of type anomaly-detection is selected', () => {
    cy.get(`[data-testid="${labelPerformanceGraphAD}"]`).should('be.visible');
  });

  it('displays the modal of edit anomaly-detection when wrench icon is clicked', () => {
    cy.get(`[data-testid="${labelPerformanceGraphAD}"]`).click();

    cy.get('[data-testid="modalEditAnomalyDetection"]').should('be.visible');
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
    });
  });

  it('displays the threshold  when add or minus buttons are clicked on slider of modal edit anomaly detection', () => {
    cy.get(`[data-testid="${labelPerformanceGraphAD}"]`).click();
    cy.wait('@getGraphDataAnomalyDetection');

    cy.get('[data-testid="add"]').click();
    cy.matchImageSnapshot();

    cy.get('[data-testid="cancel"]').click();
    cy.matchImageSnapshot();

    cy.get('[data-testid="remove"]').click();
    cy.matchImageSnapshot();
  });

  it('displays the new values of slider when add or minus buttons of slider are clicked', () => {
    cy.get(`[data-testid="${labelPerformanceGraphAD}"]`).click();

    cy.get('[data-testid="add"]').click();

    cy.fixture('resources/detailsAnomalyDetection.json').then((data) => {
      cy.get('.MuiSlider-valueLabelLabel')
        .contains(data.sensitivity.current_value + 0.1)
        .should('be.visible');
    });

    cy.get('[data-testid="cancel"]').click();

    cy.get('[data-testid="remove"]').click();
    cy.fixture('resources/detailsAnomalyDetection.json').then((data) => {
      cy.get('.MuiSlider-valueLabelLabel')
        .contains(data.sensitivity.current_value - 0.1)
        .should('be.visible');
    });
  });

  it('displays the default value on slider mark when use default value is checked', () => {
    cy.get(`[data-testid="${labelPerformanceGraphAD}"]`).click();
    cy.get('[data-testid="add"]').click();
    cy.get('[data-testid="add"]').click();

    cy.contains('use default value').click();
    cy.fixture('resources/detailsAnomalyDetection.json').then((data) => {
      cy.get('.MuiSlider-valueLabelLabel')
        .contains(data.sensitivity.default_value)
        .should('be.visible');
    });
  });

  it('displays the modal of confirmation when clicking on save button of slider', () => {
    cy.get(`[data-testid="${labelPerformanceGraphAD}"]`).click();
    cy.get('[data-testid="add"]').click();
    cy.get('[data-testid="save"]').click();
    cy.get('[data-testid=modalConfirmation]').should('be.visible');
    cy.contains(labelEditAnomalyDetectionConfirmation).should('be.visible');
    cy.matchImageSnapshot();
  });
});

describe('Anomaly detection - Global', () => {
  beforeEach(() => {
    cy.viewport(1200, 750);
    cy.fixture('resources/resourceListing.json').as('listResource');
    cy.fixture('resources/userFilter.json').as('userFilter');
    cy.fixture('resources/detailsAnomalyDetection.json').as(
      'detailsAnomalyDetection',
    );
    cy.fixture('resources/performanceGraphAnomalyDetection.json').as(
      'graphAnomalyDetection',
    );
    cy.server();
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

  it('displays the wrench icon on graph actions when one row of resource anomaly-detection is clicked', () => {
    cy.contains('ad').click();
    cy.get('[data-testid="3"]').contains(labelGraph).click();
    cy.wait('@getGraphDataAnomalyDetection');
    cy.matchImageSnapshot();
    cy.get(`[aria-label="Close"]`).click();
  });

  it('displays the modal of edit anomaly-detection when wrench icon is clicked', () => {
    cy.contains('ad').click();
    cy.get('[data-testid="3"]').click();
    cy.wait('@getGraphDataAnomalyDetection');
    cy.get(`[data-testid="${labelPerformanceGraphAD}"]`).click();
    cy.wait('@getGraphDataAnomalyDetection');
    cy.matchImageSnapshot();
    cy.get('[data-testid="closeEditModal"]').click();
    cy.get(`[aria-label="Close"]`).click();
  });

  it('displays the Anomaly detection criteria value when the type criteria chip is clicked and centreon-anomaly-detection is installed', () => {
    cy.displayFilterMenu();
    cy.matchImageSnapshot();
  });

  it('displays the  filters of anomaly detection on search proposition when user types type: in searchBar', () => {
    cy.get('input[placeholder=Search]').type('type:');
    cy.matchImageSnapshot();
    cy.get('[data-testid="Clear filter"]').click();
  });

  it('displays resources of type anomaly-detection when  filters of anomaly detection are checked and search button is clicked', () => {
    cy.fixture('resources/resourceListingByTypeAnomalyDetection.json').as(
      'listResourceByType',
    );
    cy.route('GET', '**/resources?*', '@listResourceByType').as(
      'getResourceListByType',
    );
    cy.displayFilterMenu();
    filtersToBeDisplayedInTypeMenu.map((item) => cy.contains(item).click());
    cy.get('[data-testid="Search"]').click();
    cy.matchImageSnapshot();
  });
});
