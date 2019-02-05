import axios from "axios";
import * as actions from "../actions/axiosActions";
import {
  put,
  takeLatest,
  takeEvery,
  all,
  fork,
  take,
  call
} from "redux-saga/effects";
import { eventChannel, END } from "redux-saga";

export function* getAxiosData() {
  yield takeLatest(actions.GET_DATA, axiosRequest);
}

export function* postAxiosData() {
  yield takeLatest(actions.POST_DATA, axiosRequest);
}

export function* putAxiosData() {
  yield takeLatest(actions.PUT_DATA, axiosRequest);
}

export function* deleteAxiosData() {
  yield takeLatest(actions.DELETE_DATA, axiosRequest);
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

  for (let file of files) {
    data.append("file[]", file);
  }

  const config = {
    onUploadProgress: onProgress,
    withCredentials: true,
    headers: {
      "Content-Type": "multipart/form-data"
    }
  };

  return axios.post(url, data, config);
}

function createUploader(action) {
  let emit;
  const channel = eventChannel(emitter => {
    emit = emitter;
    return () => {};
  });

  const uploadProgressCb = event => {
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
        successed: []
      }
    };
    const responses = yield all(
      action.files.map((file, idx) =>
        call(uploadSource, { ...action, files: [file], fileIndex: idx })
      )
    );

    for (let response of responses) {
      if (response.result.errors) {
        data = {
          status: true,
          result: {
            ...data.result,
            errors: [...data.result.errors, ...response.result.errors]
          }
        };
      }
      if (response.result.successed) {
        data = {
          status: true,
          result: {
            ...data.result,
            successed: [...data.result.successed, ...response.result.successed]
          }
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
      throw "Request type is required!";
    } else {
      const res = yield axios[action.requestType.toLowerCase()](
        action.url,
        action.data ? action.data : null
      );

      const data = yield res.data;

      const { propKey } = action;

      if (propKey) {
        yield put({ type: actions.SET_AXIOS_DATA, data, propKey: propKey });
      }
      if (data.status) {
        action.resolve(data);
      } else {
        action.reject(
          `${action.requestType} Request returned false status with no results!`
        );
      }
    }
  } catch (err) {
    action.reject(err);
  }
}
