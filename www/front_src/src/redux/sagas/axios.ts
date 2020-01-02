/* eslint-disable no-restricted-syntax */
/* eslint-disable no-use-before-define */

import axios from 'axios';
import { put, takeEvery, all, fork, take, call } from 'redux-saga/effects';
import { eventChannel, END } from 'redux-saga';
import * as actions from '../actions/axiosActions.ts';

export function* getAxiosData(): void {
  yield takeEvery(actions.GET_DATA, axiosRequest);
}

export function* postAxiosData(): void {
  yield takeEvery(actions.POST_DATA, axiosRequest);
}

export function* putAxiosData(): void {
  yield takeEvery(actions.PUT_DATA, axiosRequest);
}

export function* deleteAxiosData(): void {
  yield takeEvery(actions.DELETE_DATA, axiosRequest);
}

export function* uploadAxiosData(): void {
  yield takeEvery(actions.UPLOAD_DATA, uploadRequest);
}

export function* resetUploadProgress(): void {
  yield takeEvery(actions.RESET_UPLOAD_PROGRESS_DATA, resetProgress);
}

function* resetProgress(action: object): void {
  try {
    yield put({ type: actions.FILE_UPLOAD_PROGRESS, data: { reset: true } });
    action.resolve();
  } catch (err) {
    action.reject(err);
  }
}

interface Upload {
  files: Array;
  url: string;
}

function upload({ files, url }: Upload, onProgress: Function): Promise {
  const data = new FormData();

  for (const file of files) {
    data.append('file[]', file);
  }

  const config = {
    onUploadProgress: onProgress,
    withCredentials: true,
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  };

  return axios.post(url, data, config);
}

function createUploader(action: object) {
  let emit;
  const channel = eventChannel((emitter) => {
    emit = emitter;
    return () => {};
  });

  const uploadProgressCb = (event: object): Array => {
    const { total, loaded } = event;
    const percentage = Math.round((loaded * 100) / total);
    emit({ [action.fileIndex]: percentage });
    if (percentage === 100) emit(END);
  };

  const uploadPromise = upload(action, uploadProgressCb);

  return [uploadPromise, channel];
}

function* watchOnProgress(channel): void {
  while (true) {
    const data = yield take(channel);
    yield put({ type: actions.FILE_UPLOAD_PROGRESS, data });
  }
}

function* uploadRequest(action: object): void {
  try {
    let data = {
      status: false,
      result: {
        errors: [],
        successed: [],
      },
    };
    const responses = yield all(
      action.files.map((file, idx) =>
        call(uploadSource, { ...action, files: [file], fileIndex: idx }),
      ),
    );

    for (const response of responses) {
      if (response.result.errors) {
        data = {
          status: true,
          result: {
            ...data.result,
            errors: [...data.result.errors, ...response.result.errors],
          },
        };
      }
      if (response.result.successed) {
        data = {
          status: true,
          result: {
            ...data.result,
            successed: [...data.result.successed, ...response.result.successed],
          },
        };
      }
    }
    action.resolve(data);
  } catch (err) {
    action.reject(err);
  }
}

function* uploadSource(action: object): object {
  const [uploadPromise, channel] = yield call(createUploader, action);
  yield fork(watchOnProgress, channel);

  try {
    const res = yield call(() => uploadPromise);
    const data = yield res.data;
    return data;
  } catch (err) {
    throw err;
  }
}

function* axiosRequest(action: object): void {
  try {
    if (!action.requestType) {
      throw new Error('Request type is required!');
    } else {
      let dataBody = null;
      if (action.requestType === 'DELETE') {
        dataBody = action.data ? { data: action.data } : null;
      } else {
        dataBody = action.data ? action.data : null;
      }
      const res = yield axios[action.requestType.toLowerCase()](
        action.url,
        dataBody || null,
      );

      const data = yield res.data;

      const { propKey } = action;
      if (propKey) {
        yield put({ type: actions.SET_AXIOS_DATA, data, propKey });
      }
      if (data) {
        action.resolve(data);
      } else {
        action.reject('No data in response');
      }
    }
  } catch (err) {
    action.reject(err);
  }
}
