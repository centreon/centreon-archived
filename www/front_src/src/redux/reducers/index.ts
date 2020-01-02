import { combineReducers } from 'redux';
import { connectRouter } from 'connected-react-router';
import { reducer as formReducer } from 'redux-form';
import { i18nReducer } from 'react-redux-i18n';

import pollerWizardReducer from './pollerWizardReducer.ts';
import navigationReducer from './navigationReducer.ts';
import refreshReducer from './refreshReducer.ts';
import axiosReducer from './axiosReducer.ts';
import externalComponentsReducer from './externalComponentsReducer.ts';
import tooltipReducer from './tooltipReducer.ts';
import bamConfigurationReducer from './bamConfigurationReducer.ts';
import globalsReducer from './globalsReducer.ts';

export interface ReduxState {
  router: Function;
  form: Function;
  pollerForm: Function;
  i18n: Function;
  navigation: Function;
  intervals: Function;
  remoteData: Function;
  externalComponents: Function;
  tooltip: Function;
  bamConfiguration: Function;
  globals: Function;
}

export default (history) =>
  combineReducers({
    router: connectRouter(history),
    form: formReducer,
    pollerForm: pollerWizardReducer,
    i18n: i18nReducer,
    navigation: navigationReducer,
    intervals: refreshReducer,
    remoteData: axiosReducer,
    externalComponents: externalComponentsReducer,
    tooltip: tooltipReducer,
    bamConfiguration: bamConfigurationReducer,
    globals: globalsReducer,
  });
