import axios from 'axios';
import * as actions from '../actions/axiosActions'
import { put, takeLatest, fork } from 'redux-saga/effects';

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

function* axiosRequest(action) {
    try {
        if (!action.requestType) {
            throw 'Request type is required!'
        } else {
            const res = yield axios[
                action.requestType.toLowerCase()
            ](action.url, action.data ? action.data : null);

            const data = yield res.data;

            const { propKey } = action;

            if (propKey) {
                yield put({ type: actions.SET_AXIOS_DATA, data, propKey: propKey })
            }
            if (data.status) {
                action.resolve(data)
            } else {
                action.reject(`${action.requestType} Request returned false status with no results!`)
            }
        }
    }
    catch (err) {
        action.reject(err)
    }
}

