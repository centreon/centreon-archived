import { combineReducers } from "redux";
import { reducer as formReducer } from "redux-form";
import { i18nReducer } from 'react-redux-i18n';

import pollerWizardReducer from "./pollerWizardReducer";
import navigationReducer from "./navigationReducer";

export default combineReducers({
  form: formReducer,
  pollerForm: pollerWizardReducer,
  i18n: i18nReducer,
  navigation: navigationReducer,
});
