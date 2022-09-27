/* eslint-disable @typescript-eslint/no-namespace */
import 'cypress-wait-until';
import { refreshButton } from '../integration/Resources-status/common';
import { apiActionV1, executeActionViaClapi } from '../commons';

interface GetLabelByTagProps {
  label: string;
  tag?: string;
}
Cypress.Commands.add(
  'getLabelByTag',
  ({ tag = '', label }: GetLabelByTagProps): Cypress.Chainable => {
    return cy.get(`${tag}[aria-label="${label}"]`);
  },
);

Cypress.Commands.add('refreshListing', (): Cypress.Chainable => {
  return cy.get(refreshButton).click();
});

Cypress.Commands.add('removeResourceData', (): Cypress.Chainable => {
  return executeActionViaClapi({
    action: 'DEL',
    object: 'HOST',
    values: 'test_host',
  });
});

Cypress.Commands.add('setUserTokenApiV1', (): Cypress.Chainable => {
  return cy.fixture('users/admin.json').then((userAdmin) => {
    return cy
      .request({
        body: {
          password: userAdmin.password,
          username: userAdmin.login,
        },
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        method: 'POST',
        url: `${apiActionV1}?action=authenticate`,
      })
      .then(({ body }) =>
        window.localStorage.setItem('userTokenApiV1', body.authToken),
      );
  });
});

declare global {
  namespace Cypress {
    interface Chainable {
      getLabelByTag: ({ tag, label }) => Cypress.Chainable;
      refreshListing: () => Cypress.Chainable;
      removeResourceData: () => Cypress.Chainable;
      setUserTokenApiV1: () => Cypress.Chainable;
    }
  }
}
