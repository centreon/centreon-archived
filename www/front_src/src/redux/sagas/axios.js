import axios from 'axios';
import * as actions from '../actions/axiosActions'
import { put, takeLatest } from 'redux-saga/effects';

export function* getAxiosData() {
    yield takeLatest(actions.GET_DATA, fetchAxiosRequest);
}

function* fetchAxiosRequest(action) {
    try {
        const res = yield axios.get(action.url);
        const data = yield res.data;
        yield put({ type: actions.SET_AXIOS_DATA, data, propKey: action.propKey })
    }
    catch (err) {
        yield put({ type: actions.GET_DATA_ERROR, err })
    }
}