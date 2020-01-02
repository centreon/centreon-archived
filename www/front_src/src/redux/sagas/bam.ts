import { put, takeEvery } from 'redux-saga/effects';
import * as actions from '../actions/bamConfigurationActions.ts';

function* setConfiguration({ configuration }: object): void {
  try {
    yield put({ type: actions.SET_BA_CONFIGURATION, configuration });
  } catch (err) {
    throw err;
  }
}

export function* setBaConfiguration(): void {
  yield takeEvery(actions.BA_CONFIGURATION_CHANGED, setConfiguration);
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

export function* setBaConfigurationErrors(): void {
  yield takeEvery(actions.BA_CONFIGURATION_ERRORS, setErrors);
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

export function* removeErrorByKey(): void {
  yield takeEvery(actions.REMOVE_BA_CONFIGURATION_ERROR, removeError);
}
