/* eslint-disable no-restricted-syntax */
/* eslint-disable no-use-before-define */

import axios from 'axios';
import { put, takeEvery, all, fork, take, call } from 'redux-saga/effects';
import { eventChannel, END } from 'redux-saga';
import * as actions from '../actions/axiosActions';

export function* getAxiosData() {
  yield takeEvery(actions.GET_DATA, axiosRequest);
}

export function* postAxiosData() {
  yield takeEvery(actions.POST_DATA, axiosRequest);
}

export function* putAxiosData() {
  yield takeEvery(actions.PUT_DATA, axiosRequest);
}

export function* deleteAxiosData() {
  yield takeEvery(actions.DELETE_DATA, axiosRequest);
}

export function* uploadAxiosData() {
  yield takeEvery(actions.UPLOAD_DATA, uploadRequest);
}

export function* resetUploadProgress() {
  yield takeEvery(actions.RESET_UPLOAD_PROGRESS_DATA, resetProgress);
}

function* resetProgress(action) {
  try {
    yield put({ type: actions.FILE_UPLOAD_PROGRESS, data: { reset: true } });
    action.resolve();
  } catch (err) {
    action.reject(err);
  }
}

function upload({ files, url }, onProgress) {
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

function createUploader(action) {
  let emit;
  const channel = eventChannel((emitter) => {
    emit = emitter;
    return () => {};
  });

  const uploadProgressCb = (event) => {
    const { total, loaded } = event;
    const percentage = Math.round((loaded * 100) / total);
    emit({ [action.fileIndex]: percentage });
    if (percentage === 100) emit(END);
  };

  const uploadPromise = upload(action, uploadProgressCb);

  return [uploadPromise, channel];
}

function* watchOnProgress(channel) {
  while (true) {
    const data = yield take(channel);
    yield put({ type: actions.FILE_UPLOAD_PROGRESS, data });
  }
}

function* uploadRequest(action) {
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

function* uploadSource(action) {
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

function* axiosRequest(action) {
  try {
    if (!action.requestType) {
      throw new Error('Request type is required!');
    } else {
      let dataBody = null;
      if(action.requestType === "DELETE"){
        dataBody = action.data ? { data:action.data } : null
      }else{
        dataBody = action.data ? action.data : null
      }
      const res = yield axios[action.requestType.toLowerCase()](
        action.url,
        dataBody ? dataBody : null
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
