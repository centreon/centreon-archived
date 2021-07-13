import { refreshListing, resourcesMatching } from './centreonData';

let testCount = 0;

const countServicesDB = (): void => {
  cy.log('Checking in database');
  cy.task('checkServicesInDatabase', `${Cypress.env('dockerName')}`).then(
    (stdout: any): Cypress.Chainable<any> | null => {
      const string = stdout.split('\n')[1];

      cy.log('stdout : ', stdout);
      cy.log('string extracted : ', string);

      const count = parseInt(string, 10);
      testCount += 1;

      cy.log('responses found: ', count);

      if (count > 0) {
        return refreshListing().then(() => resourcesMatching());
      }
      if (testCount < 200) {
        countServicesDB();
      }

      return null;
    },
  );
};
export { countServicesDB };
