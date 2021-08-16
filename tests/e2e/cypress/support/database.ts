import {
  refreshListing,
  fixtureResourcesShouldBeDisplayed,
} from './centreonData';

let testCount = 0;

const countServicesInDatabase = (): void => {
  cy.log('Checking in database');
  cy.task('checkServicesInDatabase', `${Cypress.env('dockerName')}`).then(
    (stdout: any): Cypress.Chainable<string> | null => {
      let foundServiceCount = 0;

      cy.log(stdout);

      if (stdout !== '') {
        foundServiceCount = parseInt(stdout.split('\n')[1], 10);
      }
      testCount += 1;

      cy.log('responses found: ', foundServiceCount);
      cy.log('test count: ', testCount);

      // if (count > 0) {
      //   return refreshListing().then(() => fixtureResourcesShouldBeDisplayed());
      // }

      // if (testCount === 50) {
      //   refreshListing();
      // }
      if (testCount < 100) {
        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(500, { log: false });
        countServicesInDatabase();
      }

      return null;
    },
  );
};
export { countServicesInDatabase };
