const containerStateFilter = '[aria-label="State filter"]';
const btnToogleCriterias = '[aria-label="Show criterias filters"]';
const inputSearch = 'input[placeholder="Search"]';
const refreshButton = '[aria-label="Refresh"]';
const serviceName = 'service_test';
const serviceNameDowntime = 'service_test_dt';
const searchValue = `s.description:${serviceName}`;
const apiMonitoringResources = /.+api\/beta\/monitoring\/resources.?page.+/;

const bgCssColors = {
  acknowledge: 'rgb(247, 244, 229)',
  inDowntime: 'rgb(249, 231, 255)',
};
const actions = {
  acknowledge: 'Acknowledge',
  setDowntime: 'Set downtime',
};

export {
  containerStateFilter,
  btnToogleCriterias,
  inputSearch,
  refreshButton,
  serviceName,
  serviceNameDowntime,
  searchValue,
  apiMonitoringResources,
  bgCssColors,
  actions,
};
