import * as actions from '../actions/pollerWizardActions';
import {ReduxState} from '.';

const initialState = {};

const pollerWizardReducer = (state: object = initialState, action: object): ReduxState => {
  switch (action.type) {
    case actions.SET_POLLER_WIZARD_DATA:
      return { ...state, ...action.pollerData };
    default:
      return state;
  }
};

export default pollerWizardReducer;
