import {
  apiLogin,
  apiFilterResources,
  apiMonitoring,
  clapiFixturesPath,
} from './model';

interface Criterias {
  name: string;
  value: Array<{ id: string; name: string }>;
  type: string;
  object_type: string | null;
}

interface Filters {
  name: string;
  criterias: Array<Criterias>;
}

const insertResources = (): Cypress.Chainable =>
  cy.exec(
    `docker exec ${Cypress.env(
      'dockerName',
    )} centreon -u admin -p centreon -i ${clapiFixturesPath}/resources.txt`,
  );

const setUserTokenApi = (): Cypress.Chainable => {
  return cy.fixture('users/admin.json').then((userAdmin) => {
    return cy
      .request({
        method: 'POST',
        url: apiLogin,
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
        window.localStorage.setItem('userTokenApi', body.security.token),
      );
  });
};

const setFiltersUser = (rqMethod: string, body: Filters): Cypress.Chainable => {
  return cy
    .request({
      method: rqMethod || 'GET',
      url: apiFilterResources,
      headers: {
        'X-Auth-Token': window.localStorage.getItem('userTokenApi'),
      },
      body,
    })
    .then((response) => {
      expect(response.status).to.eq(200);
      window.localStorage.setItem('filterUserId', response.body.id);
    });
};

const delFiltersUser = (): Cypress.Chainable => {
  return cy
    .request({
      method: 'DELETE',
      url: `${apiFilterResources}/${window.localStorage.getItem(
        'filterUserId',
      )}`,
      headers: {
        'X-Auth-Token': window.localStorage.getItem('userTokenApi'),
      },
    })
    .then((response) => expect(response.status).to.eq(204));
};

const checkServiceApi = (
  hostId: number,
  serviceId: number,
): Cypress.Chainable => {
  return cy
    .request({
      method: 'POST',
      url: `${apiMonitoring}/hosts/${hostId}/services/${serviceId}/check`,
      headers: {
        'X-Auth-Token': window.localStorage.getItem('userTokenApi'),
      },
    })
    .then(({ body }) =>
      window.localStorage.setItem('userTokenApi', body.security.token),
    );
};

export {
  setUserTokenApi,
  insertResources,
  setFiltersUser,
  delFiltersUser,
  checkServiceApi,
};
