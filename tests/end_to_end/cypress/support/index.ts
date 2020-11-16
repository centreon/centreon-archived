import './commands'

beforeEach(() => {
  cy.dockerStart();
});

afterEach(() => {
// Added this time to delay the end of video recording
  cy.wait(5000)
});
