const containerStateFilter = '[aria-label="State filter"]';
const labelClearFilter = 'Clear';
const inputSearch = 'input[placeholder="Search"]';
const serviceName = 'Ping';
const searchValue = `s.description:${serviceName}`;

const selectFilterOnInput = (filterValue) => {
  cy.get(containerStateFilter).should('be.visible').click();
  cy.get(`li[data-value="${filterValue}"]`).click();
};

const pageAuthorizedToUser = () =>
  cy.get('nav[aria-label="Breadcrumb"]').should('be.visible');

const toggleCriteras = () =>
  cy.get('[aria-label="Show criterias filters"]').click();

const isServiceDisplayOnTable = () =>
  cy.get('table').contains(serviceName).should('be.visible');

const isInputContainServiceName = () =>
  cy.get(inputSearch).should('contain.value', serviceName);

export {
  containerStateFilter,
  inputSearch,
  searchValue,
  labelClearFilter,
  selectFilterOnInput,
  pageAuthorizedToUser,
  toggleCriteras,
  isServiceDisplayOnTable,
  isInputContainServiceName,
};
