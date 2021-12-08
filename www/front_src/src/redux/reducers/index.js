import { combineReducers } from 'redux';
import { reducer as formReducer } from 'redux-form';

import pollerWizardReducer from './pollerWizardReducer';

export default () =>
  combineReducers({
    form: formReducer,
    pollerForm: pollerWizardReducer,
  });
