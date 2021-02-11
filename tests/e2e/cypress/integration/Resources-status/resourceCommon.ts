const containerStateFilter = 'div[aria-label="State filter"]';
const defaultfilterValue = 'unhandled_problems';
const labelClearFilter = 'Clear';

const selectFilterOnInput = (filterValue = defaultfilterValue) => {
  const inputFilter = 'input[aria-label="MuiSelect-nativeInput"]';

  cy.get(inputFilter).should('not.be.empty');

  cy.get(containerStateFilter).should('be.visible').click();
  cy.get(`li[data-value="${filterValue}"]`).click();
};

export {
  containerStateFilter,
  defaultfilterValue,
  labelClearFilter,
  selectFilterOnInput,
};
