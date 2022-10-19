/* eslint-disable @typescript-eslint/no-namespace */

import 'cypress-wait-until';
import { refreshButton } from '../integration/Resources-status/common';
import { apiActionV1, executeActionViaClapi, ActionClapi } from '../commons';

const apiLogout = '/centreon/api/latest/authentication/logout';
const apiLoginV2 = '/centreon/authentication/providers/configurations/local';

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
  'loginByTypeOfUser',
  ({ jsonName, preserveToken }): Cypress.Chainable => {
    if (preserveToken) {
      cy.fixture(`users/${jsonName}.json`)
        .then((user) => {
          return cy.request({
            body: {
              login: user.login,
              password: user.password,
            },
            method: 'POST',
            url: apiLoginV2,
          });
        })
        .then(() => {
          Cypress.Cookies.defaults({
            preserve: 'PHPSESSID',
          });
        });
    }

    return cy
      .fixture(`users/${jsonName}.json`)
      .then((credential) => {
        cy.getByLabel({ label: 'Alias', tag: 'input' }).type(credential.login);
        cy.getByLabel({ label: 'Password', tag: 'input' }).type(
          credential.password,
        );
      })
      .getByLabel({ label: 'Connect', tag: 'button' })
      .click();
  },
);

Cypress.Commands.add(
  'hoverRootMenuItem',
  (rootItemNumber: number): Cypress.Chainable => {
    return cy.get('li').eq(rootItemNumber).trigger('mouseover');
  },
);

Cypress.Commands.add(
  'executeCommandsViaClapi',
  (fixtureFile: string): Cypress.Chainable => {
    return cy.fixture(fixtureFile).then((listRequestConfig) => {
      cy.wrap(
        Promise.all(
          listRequestConfig.map((request: ActionClapi) =>
            executeActionViaClapi(request),
          ),
        ),
      );
    });
  },
);

Cypress.Commands.add('getIframeBody', (): Cypress.Chainable => {
  return cy
    .get('iframe#main-content')
    .its('0.contentDocument.body')
    .should('not.be.empty')
    .then(cy.wrap);
});

Cypress.Commands.add(
  'getFormFieldByIndex',
  (rootItemNumber: number): Cypress.Chainable => {
    return cy.getIframeBody().find('#Form').find('tr').eq(rootItemNumber);
  },
);

Cypress.Commands.add(
  'isInProfileMenu',
  (targetedMenu: string): Cypress.Chainable => {
    return cy
      .get('header')
      .get('svg[aria-label="Profile"]')
      .click()
      .get('div[role="tooltip"]')
      .contains(targetedMenu);
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

Cypress.Commands.add('logout', (): Cypress.Chainable => {
  return cy.request({
    body: {},
    method: 'POST',
    url: apiLogout,
  });
});

Cypress.Commands.add('removeACL', (): Cypress.Chainable => {
  return cy.setUserTokenApiV1().then(() => {
    executeActionViaClapi({
      action: 'DEL',
      object: 'ACLMENU',
      values: 'acl_menu_test',
    });
    executeActionViaClapi({
      action: 'DEL',
      object: 'ACLGROUP',
      values: 'ACL Group test',
    });
  });
});

interface GetByLabelProps {
  label: string;
  tag?: string;
}

interface NavigateToProps {
  page: string;
  rootItemNumber: number;
  subMenu?: string;
}

interface LoginByTypeOfUserProps {
  jsonName?: string;
  preserveToken?: boolean;
}

declare global {
  namespace Cypress {
    interface Chainable {
      executeCommandsViaClapi: (fixtureFile: string) => Cypress.Chainable;
      getByLabel: ({ tag, label }: GetByLabelProps) => Cypress.Chainable;
      getFormFieldByIndex: (rootItemNumber: number) => Cypress.Chainable;
      getIframeBody: () => Cypress.Chainable;
      hoverRootMenuItem: (rootItemNumber: number) => Cypress.Chainable;
      isInProfileMenu: (targetedMenu: string) => Cypress.Chainable;
      loginByTypeOfUser: ({
        jsonName = 'admin',
        preserveToken = false,
      }: LoginByTypeOfUserProps) => Cypress.Chainable;
      logout: () => Cypress.Chainable;
      navigateTo: ({
        page,
        rootItemNumber,
        subMenu,
      }: NavigateToProps) => Cypress.Chainable;
      refreshListing: () => Cypress.Chainable;
      removeACL: () => Cypress.Chainable;
      removeResourceData: () => Cypress.Chainable;
      setUserTokenApiV1: () => Cypress.Chainable;
    }
  }
}
