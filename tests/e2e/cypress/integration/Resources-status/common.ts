const stateFilterContainer = '[aria-label="State filter"]';
const searchInput = 'input[placeholder="Search"]';
const refreshButton = '[aria-label="Refresh"]';
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
  resourceMonitoringApi,
  actionBackgroundColors,
  actions,
};
