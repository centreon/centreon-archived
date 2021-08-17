import {
  applyConfigurationViaClapi,
  submitResultsViaClapi,
} from './centreonData';

const stepWaitingTime = 500;
const timeout = 10000;
const maxSteps = timeout / stepWaitingTime;

let stepCount = 0;

const checkThatFixtureServicesExistInDatabase = (): void => {
  cy.log('Checking in database');
  cy.task('checkServicesInDatabase', `${Cypress.env('dockerName')}`).then(
    (stdout: any): Cypress.Chainable<null> | null => {
      let foundServiceCount = 0;

      if (stdout !== '') {
        foundServiceCount = parseInt(stdout.split('\n')[1], 10);
      }

      stepCount += 1;

      cy.log('Service count in database', foundServiceCount);
      cy.log('Service database check step count', stepCount);

      if (foundServiceCount > 0) {
        return null;
      }

      if (stepCount < maxSteps) {
        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(stepWaitingTime, { log: false });

        return cy
          .wrap(null)
          .then(() => checkThatFixtureServicesExistInDatabase());
      }

      throw new Error(`No service found in the database after ${timeout}ms`);
    },
  );
};

let configCheckStepCount = 0;

const checkThatConfigurationIsExported = (): void => {
  cy.log('Checking that configuration is exported');
  cy.task('checkServicesInDatabase', `${Cypress.env('dockerName')}`).then(
    (): Cypress.Chainable<void | Cypress.Exec | null> | null => {
      const configurationExported = false;

      configCheckStepCount += 1;

      cy.log('Configuration exported', configurationExported);
      cy.log('Configuration export check step count', configCheckStepCount);

      if (configurationExported) {
        return null;
      }

      if (configCheckStepCount < maxSteps) {
        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(stepWaitingTime, { log: false });

        return cy
          .exec(
            `docker exec -i ${Cypress.env(
              'dockerName',
            )} date -r /etc/centreon-engine/services.cfg`,
          )
          .then(({ stdout }) => {
            cy.log('export date', new Date(stdout));

            const twoMinutes = 10000;
            if (
              new Date().getTime() - new Date(stdout).getTime() <
              twoMinutes
            ) {
              return null;
            }

            return checkThatConfigurationIsExported();
          });
      }

      throw new Error(`No configuration export after ${timeout}ms`);
    },
  );
};

export {
  checkThatFixtureServicesExistInDatabase,
  checkThatConfigurationIsExported,
};
