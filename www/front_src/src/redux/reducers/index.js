import { combineReducers } from 'redux';
import { connectRouter } from 'connected-react-router';
import { reducer as formReducer } from 'redux-form';

import pollerWizardReducer from './pollerWizardReducer';
import navigationReducer from './navigationReducer';
import refreshReducer from './refreshReducer';
import externalComponentsReducer from './externalComponentsReducer';
import tooltipReducer from './tooltipReducer';
import axiosReducer from './axiosReducer';

export default (history) =>
  combineReducers({
    externalComponents: externalComponentsReducer,
    form: formReducer,
    intervals: refreshReducer,
    navigation: navigationReducer,
    pollerForm: pollerWizardReducer,
    remoteData: axiosReducer,
    router: connectRouter(history),
    tooltip: tooltipReducer,
  });
