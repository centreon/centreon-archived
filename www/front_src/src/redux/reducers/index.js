import { combineReducers } from 'redux';
import { reducer as formReducer } from 'redux-form';

import pollerWizardReducer from './pollerWizardReducer';
import navigationReducer from './navigationReducer';
import refreshReducer from './refreshReducer';
import externalComponentsReducer from './externalComponentsReducer';

export default () =>
  combineReducers({
    externalComponents: externalComponentsReducer,
    form: formReducer,
    intervals: refreshReducer,
    navigation: navigationReducer,
    pollerForm: pollerWizardReducer,
  });
