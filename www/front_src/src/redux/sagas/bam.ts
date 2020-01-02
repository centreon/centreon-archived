import axios from 'axios';
import {
  put,
  takeLatest,
  takeEvery,
  all,
  fork,
  take,
  call,
} from 'redux-saga/effects';
import * as actions from '../actions/bamConfigurationActions';
import { BamConfiguation } from '../reducers/bamConfigurationReducer';

export function* setBaConfiguration(): void {
  yield takeEvery(actions.BA_CONFIGURATION_CHANGED, setConfiguration);
}

function* setConfiguration({ configuration }: BamConfiguation): void {
  try {
    yield put({ type: actions.SET_BA_CONFIGURATION, configuration });
  } catch (err) {
    throw err;
  }
}

export function* setBaConfigurationErrors(): void {
  yield takeEvery(actions.BA_CONFIGURATION_ERRORS, setErrors);
}

interface Errors {
  errors: object;
}

function* setErrors({ errors }: Errors): void {
  try {
    yield put({ type: actions.SET_BA_CONFIGURATION_ERRORS, errors });
  } catch (err) {
    throw err;
  }
}

export function* removeErrorByKey(): void {
  yield takeEvery(actions.REMOVE_BA_CONFIGURATION_ERROR, removeError);
}

interface ErrorsWithKey {
  errors: object;
  key: string;
}

function* removeError({ errors, key }: ErrorsWithKey): void {
  try {
    yield put({ type: actions.REMOVE_BA_CONFIGURATION_ERROR, errors, key });
  } catch (err) {
    throw err;
  }
}
