import {
  applyConfigurationViaClapi,
  submitResultsViaClapi,
} from './centreonData';

const stepWaitingTime = 500;
const timeout = 100000;
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

        return (
          cy
            .wrap(null)
            // .then(() => submitResultsViaClapi())
            .then(() => checkThatFixtureServicesExistInDatabase())
        );
      }

      throw new Error(`No service found in the database after ${timeout}ms`);
    },
  );
};

let configCheckStepCount = 0;

const checkThatConfigurationIsExported = (): void => {
  cy.log('Checking that configuration is exported');
  cy.exec(`bash -c "whoami"`)
    .then((res) => {
      cy.log(res.stdout);
      return cy.exec(
        `bash -c "docker exec -i ${Cypress.env(
          'dockerName',
        )} bash -c date -r /etc/centreon-engine/hosts.cfg"`,
      );
    })
    .then(({ stdout }): Cypress.Chainable<null> | null => {
      // let configurationExported = false;
      configCheckStepCount += 1;

      const twoMinutes = 5000;
      const exported =
        new Date().getTime() - new Date(stdout).getTime() < twoMinutes;

      // configurationExported = exported as boolean;

      cy.log(stdout);
      cy.log('Configuration exported', exported);
      cy.log('Configuration export check step count', configCheckStepCount);

      if (exported) {
        return null;
      }

      if (configCheckStepCount < maxSteps) {
        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(stepWaitingTime, { log: false });

        // return cy.wrap(null)
        return applyConfigurationViaClapi().then(() =>
          checkThatConfigurationIsExported(),
        );
      }

      throw new Error(`No configuration export after ${timeout}ms`);
    });
};

export {
  checkThatFixtureServicesExistInDatabase,
  checkThatConfigurationIsExported,
};
