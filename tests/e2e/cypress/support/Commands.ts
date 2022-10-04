/* eslint-disable @typescript-eslint/no-namespace */
import 'cypress-wait-until';
import { refreshButton } from '../integration/Resources-status/common';
import { apiActionV1, executeActionViaClapi } from '../commons';

Cypress.Commands.add(
  'getByLabel',
  ({ tag = '', label }: GetByLabelProps): Cypress.Chainable => {
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

Cypress.Commands.add(
  'loginContactWithAdminCredentials',
  ({ username, password }): Cypress.Chainable => {
    cy.getByLabel({ label: 'Alias', tag: 'input' }).type(username);
    cy.getByLabel({ label: 'Password', tag: 'input' }).type(password);

    return cy.getByLabel({ label: 'Connect', tag: 'button' }).click();
  },
);

Cypress.Commands.add(
  'hoverRootMenuItem',
  (rootItemNumber: number): Cypress.Chainable => {
    return cy.get('li').eq(rootItemNumber).trigger('mouseover');
  },
);
Cypress.Commands.add('getIframeBody', (): Cypress.Chainable => {
  return cy
    .get('iframe#main-content')
    .its('0.contentDocument')
    .should('exist')
    .its('body')

    .then(cy.wrap);
});

Cypress.Commands.add(
  'getFormFieldByIndex',
  (rootItemNumber: number): Cypress.Chainable => {
    return cy.getIframeBody().find('#Form').find('tr').eq(rootItemNumber);
  },
);

Cypress.Commands.add(
  'navigateTo',
  ({ rootItemNumber, subMenu, page }): void => {
    if (subMenu) {
      cy.hoverRootMenuItem(rootItemNumber)
        .contains(subMenu)
        .trigger('mouseover');
      cy.contains(page).click();

      return;
    }
    cy.hoverRootMenuItem(rootItemNumber).contains(page).click();
  },
);

interface GetByLabelProps {
  label: string;
  tag?: string;
}

interface AdminCredentialsProps {
  password: string;
  username: string;
}

interface NavigateToProps {
  page: string;
  rootItemNumber: number;
  subMenu?: string;
}

declare global {
  namespace Cypress {
    interface Chainable {
      getByLabel: ({ tag, label }: GetByLabelProps) => Cypress.Chainable;
      getFormFieldByIndex: (rootItemNumber: number) => Cypress.Chainable;
      getIframeBody: () => Cypress.Chainable;
      hoverRootMenuItem: (rootItemNumber: number) => Cypress.Chainable;
      loginContactWithAdminCredentials: ({
        username,
        password,
      }: AdminCredentialsProps) => Cypress.Chainable;
      navigateTo: ({
        page,
        rootItemNumber,
        subMenu,
      }: NavigateToProps) => Cypress.Chainable;
      refreshListing: () => Cypress.Chainable;
      removeResourceData: () => Cypress.Chainable;
      setUserTokenApiV1: () => Cypress.Chainable;
    }
  }
}
