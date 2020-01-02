export const SET_POLLER_WIZARD_DATA = '@poller/SET_POLLER_WIZARD_DATA';

export const setPollerWizard = (pollerData): object => ({
  type: SET_POLLER_WIZARD_DATA,
  pollerData,
});
