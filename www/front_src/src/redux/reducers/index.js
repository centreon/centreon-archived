import { combineReducers } from 'redux';
import { connectRouter } from 'connected-react-router';
import { reducer as formReducer } from 'redux-form';
import { i18nReducer } from 'react-redux-i18n';

import pollerWizardReducer from './pollerWizardReducer';
import navigationReducer from './navigationReducer';
import refreshReducer from './refreshReducer';
import axiosReducer from './axiosReducer';
import externalComponentsReducer from './externalComponentsReducer';
import tooltipReducer from './tooltipReducer';
import bamConfigurationReducer from './bamConfigurationReducer';
import globalsReducer from './globalsReducer';

export default (history) => combineReducers({
  router: connectRouter(history),
  form: formReducer,
  pollerForm: pollerWizardReducer,
  i18n: i18nReducer,
  navigation: navigationReducer,
  intervals: refreshReducer,
  remoteData: axiosReducer,
  externalComponents: externalComponentsReducer,
  tooltip: tooltipReducer,
  bamConfiguration:bamConfigurationReducer,
  globals:globalsReducer
});
