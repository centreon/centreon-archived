import * as actions from '../actions/pollerWizardActions.ts';

const initialState = {};

const pollerWizardReducer = (
  state: object = initialState,
  action: object,
): object => {
  // to be remplaced by ReduxState when when types definition will be included
  switch (action.type) {
    case actions.SET_POLLER_WIZARD_DATA:
      return { ...state, ...action.pollerData };
    default:
      return state;
  }
};

export default pollerWizardReducer;
