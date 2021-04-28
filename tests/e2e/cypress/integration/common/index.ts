import { Given } from 'cypress-cucumber-preprocessor/steps';

const fixtureClapiPath = 'cypress/fixtures/clapi';

// Background
Given('There are available resources', () => {
  cy.readFile(`${fixtureClapiPath}/resources.txt`).then((data) => {
    const linesResources = data.split('\n').filter((d) => d.includes('ADD'));

    const resources = linesResources.map((line: string) => {
      const [name, description] = line
        .split(';')
        .filter((_, index: number) => index === 2 || index === 3);
      return { name, description };
    });
    cy.wrap(resources).as('resources');
  });
});
