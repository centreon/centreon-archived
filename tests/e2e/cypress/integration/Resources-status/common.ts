const containerStateFilter = '[aria-label="State filter"]';
const btnToogleCriterias = '[aria-label="Show criterias filters"]';
const inputSearch = 'input[placeholder="Search"]';
const serviceName = 'service_test';
const searchValue = `s.description:${serviceName}`;

const canAccessPage = (): boolean => true;
const validUserAccount = (): boolean => true;

export {
  containerStateFilter,
  btnToogleCriterias,
  inputSearch,
  searchValue,
  canAccessPage,
  validUserAccount,
};
