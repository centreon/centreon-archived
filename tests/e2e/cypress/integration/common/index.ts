import { Given } from 'cypress-cucumber-preprocessor/steps';

// Background
Given('There are available resources', () => {
  cy.readFile('cypress/fixtures/resources.txt').then((data) => {
    const resourceLines = data.split('\n').filter((d) => d.includes('ADD'));

    const resources = resourceLines.map((line: string) => {
      const [name, description] = line
        .split(';')
        .filter((_, index: number) => index === 2 || index === 3);
      return { name, description };
    });
    cy.wrap(resources).as('resources');
  });
});
