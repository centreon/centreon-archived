import { combineReducers } from 'redux';
import { connectRouter } from 'connected-react-router';
import { reducer as formReducer } from 'redux-form';
import { i18nReducer } from 'react-redux-i18n';

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
    i18n: i18nReducer,
    intervals: refreshReducer,
    navigation: navigationReducer,
    pollerForm: pollerWizardReducer,
    remoteData: axiosReducer,
    router: connectRouter(history),
    tooltip: tooltipReducer,
  });
