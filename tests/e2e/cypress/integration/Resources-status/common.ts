const stateFilterContainer = '[aria-label="State filter"]';
const searchInput = 'input[placeholder="Search"]';
const refreshButton = '[aria-label="Refresh"]';
const serviceName = 'service_test';
const serviceNameDowntime = 'service_test_dt';
const searchValue = `s.description:${serviceName}`;
const resourceMonitoringApi = /.+api\/beta\/monitoring\/resources.?page.+/;

const actionBackgroundColors = {
  acknowledge: 'rgb(247, 244, 229)',
  inDowntime: 'rgb(249, 231, 255)',
};
const actions = {
  acknowledge: 'Acknowledge',
  setDowntime: 'Set downtime',
};

export {
  stateFilterContainer,
  searchInput,
  refreshButton,
  serviceName,
  serviceNameDowntime,
  searchValue,
  resourceMonitoringApi,
  actionBackgroundColors,
  actions,
};
