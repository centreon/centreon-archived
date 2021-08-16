import { submitResultsViaClapi } from './centreonData';

const stepWaitingTime = 500;
const timeout = 6000;
const maxSteps = timeout / stepWaitingTime;

let stepCount = 0;

const checkThatFixtureServicesExistInDatabase = (): void => {
  cy.log('Checking in database');
  cy.task('checkServicesInDatabase', `${Cypress.env('dockerName')}`).then(
    (stdout: any): Cypress.Chainable<void> | null => {
      let foundServiceCount = 0;

      if (stdout !== '') {
        foundServiceCount = parseInt(stdout.split('\n')[1], 10);
      }

      stepCount += 1;

      cy.log('Service count in database:', foundServiceCount);
      cy.log('Service database check step count:', stepCount);

      if (foundServiceCount > 0) {
        return null;
      }

      if (stepCount < maxSteps) {
        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(500, { log: false });

        return submitResultsViaClapi().then(() =>
          checkThatFixtureServicesExistInDatabase(),
        );
      }

      throw new Error(`No service found in the database after ${timeout}ms`);
    },
  );
};
export { checkThatFixtureServicesExistInDatabase };
