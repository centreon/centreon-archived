import {
  applyConfigurationViaClapi,
  executeActionViaClapi,
} from '../../commons';

interface DataToUseForCheckForm {
  custom?: () => void;
  selector: string;
  value?: string;
}

const initializeConfigACLAndGetLoginPage = (): Cypress.Chainable => {
  return cy
    .executeCommandsViaClapi(
      'resources/clapi/config-ACL/local-authentication-acl-user.json',
    )
    .then(applyConfigurationViaClapi)
    .then(() => cy.visit(`${Cypress.config().baseUrl}`))
    .then(() => cy.fixture('users/admin.json'));
};

const removeContact = (): Cypress.Chainable => {
  return cy.setUserTokenApiV1().then(() => {
    executeActionViaClapi({
      action: 'DEL',
      object: 'CONTACT',
      values: 'user1',
    });
  });
};

const checkDefaultsValueForm: Array<DataToUseForCheckForm> = [
  {
    selector: '#Minimumpasswordlength',
    value: '12',
  },
  {
    custom: (): void => {
      cy.get('#Passwordmustcontainlowercase').should(
        'have.class',
        'MuiButton-containedPrimary',
      );
    },
    selector: '#Passwordmustcontainlowercase',
    value: '',
  },
  {
    custom: (): void => {
      cy.get('#Passwordmustcontainuppercase').should(
        'have.class',
        'MuiButton-containedPrimary',
      );
    },

    selector: '#Passwordmustcontainuppercase',
    value: '',
  },
  {
    custom: (): void => {
      cy.get('#Passwordmustcontainnumbers').should(
        'have.class',
        'MuiButton-containedPrimary',
      );
    },

    selector: '#Passwordmustcontainnumbers',
    value: '',
  },
  {
    custom: (): void => {
      cy.get('#Passwordmustcontainspecialcharacters').should(
        'have.class',
        'MuiButton-containedPrimary',
      );
    },

    selector: '#Passwordmustcontainspecialcharacters',
    value: '',
  },
  {
    selector: '#PasswordexpiresafterpasswordExpirationexpirationDelayMonth',
    value: '6',
  },
  {
    selector: '#PasswordexpiresafterpasswordExpirationexpirationDelayDay',
    value: '0',
  },
  {
    custom: (): void => {
      cy.get('div[name="excludedUsers"]')
        .find('span')
        .contains('centreon-gorgone');
    },
    selector: '#Excludedusers',
    value: '',
  },
  {
    selector: '#MinimumtimebetweenpasswordchangesdelayBeforeNewPasswordDay',
    value: '0',
  },
  {
    selector: '#MinimumtimebetweenpasswordchangesdelayBeforeNewPasswordHour',
    value: '1',
  },
  {
    selector: '#Last3passwordscanbereused',
    value: 'on',
  },
  {
    selector: '#Numberofattemptsbeforeuserisblocked',
    value: '5',
  },
  {
    selector:
      '#TimethatmustpassbeforenewconnectionisallowedblockingDurationDay',
    value: '0',
  },
  {
    selector:
      '#TimethatmustpassbeforenewconnectionisallowedblockingDurationHour',
    value: '0',
  },
];

export {
  removeContact,
  initializeConfigACLAndGetLoginPage,
  checkDefaultsValueForm,
  DataToUseForCheckForm,
};
