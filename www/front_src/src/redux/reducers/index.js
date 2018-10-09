import { combineReducers } from "redux";
import { reducer as formReducer } from "redux-form";

import pollerWizardReducer from "./pollerWizardReducer";
import navigationReducer from "./navigationReducer";

export default combineReducers({
  form: formReducer,
  pollerForm: pollerWizardReducer,
  navigation: navigationReducer
});
