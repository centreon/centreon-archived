import { combineReducers } from "redux";
import { reducer as formReducer } from "redux-form";
import { i18nReducer } from 'react-redux-i18n';

import pollerWizardReducer from "./pollerWizardReducer";
import navigationReducer from "./navigationReducer";
import refreshReducer from "./refreshReducer";
import axiosReducer from "./axiosReducer";

export default combineReducers({
  form: formReducer,
  pollerForm: pollerWizardReducer,
  i18n: i18nReducer,
  navigation: navigationReducer,
  intervals: refreshReducer,
  remoteData: axiosReducer
});
