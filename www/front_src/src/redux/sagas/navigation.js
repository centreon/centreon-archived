/* eslint-disable no-use-before-define */
/* eslint-disable import/prefer-default-export */

import { takeEvery } from 'redux-saga/effects';
import * as actions from '../actions/navigationActions';

export function* loadNavigationData() {
  yield takeEvery(actions.GET_NAVIGATION_DATA, loadNavigation);
}

function loadNavigation() {
  try {
    const event = window.parent.document.createEvent('Event');
    event.initEvent(`reloadNavigation`, false, true);
    window.parent.document.dispatchEvent(event);
  } catch (err) {
    throw err;
  }
}
