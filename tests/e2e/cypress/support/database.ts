import { refreshListing, resourcesMatching } from './centreonData';

let testCount = 0;

const countServicesDB = (): void => {
  cy.log('dbCheck task');
  cy.task('dbCheck', `${Cypress.env('dockerName')}`).then((stdout: any) => {
    const string = stdout.split('\n')[1];
    const count = parseInt(string, 10);
    testCount = +1;

    if (count > 0) {
      refreshListing().then(() => resourcesMatching());
      return;
    }
    if (testCount < 5) {
      countServicesDB();
    }
  });
};
export { countServicesDB };
