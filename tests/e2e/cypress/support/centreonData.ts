import { refreshButton } from '../integration/Resources-status/common';

import { apiActionV1, apiLoginV2, apiFilterResourcesBeta } from './model';

interface Criteria {
  name: string;
  value: Array<{ id: string; name: string }>;
  type: string;
  object_type: string | null;
}
interface Filter {
  name: string;
  criterias: Array<Criteria>;
}

interface ActionClapi {
  action: string;
  object?: string;
  values: string;
}

interface Status {
  severity_code: number;
  name: string;
}
interface Resource {
  type: 'host' | 'service';
  name: string;
  status: Status;
  acknowledged: boolean;
  in_downtime: boolean;
}

const refreshListing = (timeout = 0): Cypress.Chainable => {
  // "wait" here, it's necessary to allow time for the action
  // to be taken into account before launching a call to the API.
  if (timeout > 0) cy.wait(timeout);
  cy.get(refreshButton).children('button').should('be.enabled');
  return cy.get(refreshButton).click();
};

const resourcesMatching = (): Cypress.Chainable => {
  cy.readFile('cypress/fixtures/resources.txt').then((data) => {
    const resourceLines = data.split('\n').filter((d) => d.includes('ADD'));

    const resources = resourceLines.map((line: string) => {
      const [name, description] = line
        .split(';')
        .filter((_, index: number) => index === 2 || index === 3);
      return { name, description };
    });
    cy.wrap(resources).as('resources');
  });

  return cy.get<Array<Resource>>('@resources').then((resources) => {
    resources.forEach(({ name }) => {
      cy.contains(name).should('exist');
      cy.contains('CRITICAL');
    });
  });
};

const actionClapiApi = (
  bodyContent: ActionClapi,
  method?: string,
): Cypress.Chainable => {
  return cy.request({
    method: method || 'POST',
    url: `${apiActionV1}?action=action&object=centreon_clapi`,
    body: bodyContent,
    headers: {
      'Content-Type': 'application/json',
      'centreon-auth-token': window.localStorage.getItem('userTokenApiV1'),
    },
  });
};

const setUserTokenApiV1 = (): Cypress.Chainable => {
  return cy.fixture('users/admin.json').then((userAdmin) => {
    return cy
      .request({
        method: 'POST',
        url: `${apiActionV1}?action=authenticate`,
        body: {
          username: userAdmin.login,
          password: userAdmin.password,
        },
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
      })
      .then(({ body }) =>
        window.localStorage.setItem('userTokenApiV1', body.authToken),
      );
  });
};

const setUserTokenApiV2 = (): Cypress.Chainable => {
  return cy.fixture('users/admin.json').then((userAdmin) => {
    return cy
      .request({
        method: 'POST',
        url: apiLoginV2,
        body: {
          security: {
            credentials: {
              login: userAdmin.login,
              password: userAdmin.password,
            },
          },
        },
      })
      .then(({ body }) =>
        window.localStorage.setItem('userTokenApiV2', body.security.token),
      );
  });
};

const setUserFilter = (body: Filter): Cypress.Chainable => {
  return cy
    .request({
      method: 'POST',
      url: apiFilterResourcesBeta,
      headers: {
        'X-Auth-Token': window.localStorage.getItem('userTokenApiV2'),
      },
      body,
    })
    .then((response) => {
      expect(response.status).to.eq(200);
      window.localStorage.setItem('filterUserId', response.body.id);
    });
};

const deleteUserFilter = (): Cypress.Chainable => {
  return cy
    .request({
      method: 'DELETE',
      url: `${apiFilterResourcesBeta}/${window.localStorage.getItem(
        'filterUserId',
      )}`,
      headers: {
        'X-Auth-Token': window.localStorage.getItem('userTokenApiV2'),
      },
    })
    .then((response) => expect(response.status).to.eq(204));
};

const submitResultApiClapi = (): Cypress.Chainable => {
  return cy
    .fixture('resources/clapi/submit-results.json')
    .then(({ results }) => {
      const timestampNow = Math.floor(Date.now() / 1000) - 15;

      const submitResults = results.map((res) => {
        res.updatetime = timestampNow.toString();
        return res;
      });

      return cy
        .request({
          method: 'POST',
          url: `${apiActionV1}?action=submit&object=centreon_submit_results`,
          body: { results: submitResults },
          headers: {
            'Content-Type': 'application/json',
            'centreon-auth-token':
              window.localStorage.getItem('userTokenApiV1'),
          },
        })
        .then((response) => expect(response.status).to.eq(200));
    });
};

const initDataResources = (): Cypress.Chainable => {
  cy.fixture('resources/clapi/host1/01-add.json').then((raw) =>
    actionClapiApi(raw),
  );

  cy.fixture('resources/clapi/service1/01-add.json').then((raw) =>
    actionClapiApi(raw),
  );

  cy.fixture('resources/clapi/service1/02-set-max-check.json').then((raw) =>
    actionClapiApi(raw),
  );

  cy.fixture('resources/clapi/service1/03-disable-active-check.json').then(
    (raw) => actionClapiApi(raw),
  );

  cy.fixture('resources/clapi/service1/04-enable-passive-check.json').then(
    (raw) => actionClapiApi(raw),
  );

  cy.fixture('resources/clapi/service2/01-add.json').then((raw) =>
    actionClapiApi(raw),
  );

  cy.fixture('resources/clapi/service2/02-set-max-check.json').then((raw) =>
    actionClapiApi(raw),
  );

  cy.fixture('resources/clapi/service2/03-disable-active-check.json').then(
    (raw) => actionClapiApi(raw),
  );

  return cy
    .fixture('resources/clapi/service2/04-enable-passive-check.json')
    .then((raw) => actionClapiApi(raw));
};

const removeDataResources = (): Cypress.Chainable => {
  return actionClapiApi({
    action: 'DEL',
    object: 'HOST',
    values: 'test_host',
  });
};

const applyCfgApi = (): Cypress.Chainable => {
  return actionClapiApi({
    action: 'APPLYCFG',
    values: '1',
  });
};

export {
  setUserTokenApiV1,
  setUserTokenApiV2,
  actionClapiApi,
  setUserFilter,
  deleteUserFilter,
  submitResultApiClapi,
  initDataResources,
  removeDataResources,
  applyCfgApi,
  resourcesMatching,
  refreshListing,
};
