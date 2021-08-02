import { refreshListing, resourcesMatching } from './centreonData';

let testCount = 0;

const countServicesDB = (): void => {
  cy.log('Checking in database');
  cy.task('checkServicesInDatabase', `${Cypress.env('dockerName')}`).then(
    (stdout: any): Cypress.Chainable<string> | null => {
      let count = 0;

      if (stdout !== '') {
        count = parseInt(stdout.split('\n')[1], 10);
      }
      testCount += 1;

      cy.log('responses found: ', count);
      cy.log('test count: ', testCount);

      if (count > 0) {
        return refreshListing().then(() => resourcesMatching());
      }

      if (testCount === 50) {
        refreshListing();
      }
      if (testCount < 100) {
        countServicesDB();
      }

      return null;
    },
  );
};
export { countServicesDB };
