import { refreshListing, resourcesMatching } from './centreonData';

const countServicesDB = (): void => {
  cy.task('dbCheck', `${Cypress.env('dockerName')}`).then((stdout: any) => {
    const string = stdout.split('\n')[1];
    const count = parseInt(string, 10);

    if (count > 0) {
      refreshListing().then(() => resourcesMatching());
      return;
    }

    countServicesDB();
  });
};
export { countServicesDB };
