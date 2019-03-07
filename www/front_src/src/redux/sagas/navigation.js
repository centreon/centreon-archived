import * as actions from "../actions/navigationActions";
import { takeEvery } from "redux-saga/effects";

export function* loadNavigationData() {
  yield takeEvery(actions.GET_NAVIGATION_DATA, loadNavigation);
}

function loadNavigation() {
  try {
    var event = window.parent.document.createEvent("Event");
    event.initEvent(
      `reloadNavigation`,
      false,
      true
    );
    window.parent.document.dispatchEvent(event);
  } catch (err) {
    throw err;
  }
}
