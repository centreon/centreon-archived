Cypress.Commands.add('login', () => {
  const apiVersion = 'v2';

  cy.clearCookies();
  return cy
    .fixture('users/admin')
    .as('user')
    .then((user) => {
      cy.request(
        'POST',
        `${Cypress.env('DOCKER_URL')}/centreon/api/${apiVersion}/login`,
        {
          security: { credentials: user },
        },
      ).then((resp) => {
        expect(resp.body).to.have.property('security');
        Cypress.Cookies.preserveOnce('PHPSESSID', resp.body.security.token);
      });
    });
});

Cypress.Commands.add('dockerStart', () => {
  return cy
    .exec(`npx wait-on ${Cypress.env('DOCKER_URL')}`)
    .then(() =>
      cy.log(`Docker Centreon started on  ${Cypress.env('DOCKER_URL')}`),
    );
});
