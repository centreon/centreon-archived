/* eslint-disable no-useless-catch */
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
    yield put({ data: { reset: true }, type: actions.FILE_UPLOAD_PROGRESS });
    action.resolve();
  } catch (err) {
    action.reject(err);
  }
}

const upload = ({ files, url }, onProgress) => {
  const data = new FormData();

  for (const file of files) {
    data.append('file[]', file);
  }

  const config = {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
    onUploadProgress: onProgress,
    withCredentials: true,
  };

  return axios.post(url, data, config);
};

const createUploader = (action) => {
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
};

function* watchOnProgress(channel) {
  while (true) {
    const data = yield take(channel);
    yield put({ data, type: actions.FILE_UPLOAD_PROGRESS });
  }
}

function* uploadRequest(action) {
  try {
    let data = {
      result: {
        errors: [],
        successed: [],
      },
      status: false,
    };
    const responses = yield all(
      action.files.map((file, idx) =>
        call(uploadSource, { ...action, fileIndex: idx, files: [file] }),
      ),
    );

    for (const response of responses) {
      if (response.result.errors) {
        data = {
          result: {
            ...data.result,
            errors: [...data.result.errors, ...response.result.errors],
          },
          status: true,
        };
      }
      if (response.result.successed) {
        data = {
          result: {
            ...data.result,
            successed: [...data.result.successed, ...response.result.successed],
          },
          status: true,
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
        yield put({ data, propKey, type: actions.SET_AXIOS_DATA });
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
