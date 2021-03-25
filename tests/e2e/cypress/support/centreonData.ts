import * as path from 'path';

import { apiFilterResources } from './model';

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

const clapiFixturesPath = path.resolve('/tmp/clapi/');

const insertResources = (): Cypress.Chainable =>
  cy.exec(
    `docker exec centreon-dev centreon -u admin -p centreon -i ${clapiFixturesPath}/resources.txt`,
  );

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
    .then((response) => expect(response.status).to.eq(200));
};

export { insertResources, setFiltersUser };
