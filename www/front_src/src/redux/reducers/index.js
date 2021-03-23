import pollerWizardReducer from './pollerWizardReducer';
import navigationReducer from './navigationReducer';
import refreshReducer from './refreshReducer';
import externalComponentsReducer from './externalComponentsReducer';
import tooltipReducer from './tooltipReducer';
import axiosReducer from './axiosReducer';

import { reducer as formReducer } from 'redux-form';
import { connectRouter } from 'connected-react-router';
import { combineReducers } from 'redux';

export default (history) =>
  combineReducers({
    router: connectRouter(history),
    form: formReducer,
    pollerForm: pollerWizardReducer,
    navigation: navigationReducer,
    remoteData: axiosReducer,
    intervals: refreshReducer,
    externalComponents: externalComponentsReducer,
    tooltip: tooltipReducer,
  });
