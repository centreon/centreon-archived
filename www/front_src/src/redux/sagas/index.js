import {all,fork} from 'redux-saga/effects';

import * as axiosSagas from './axios';

const rootSaga = function* rootSaga(){
    yield all([
        fork(axiosSagas.getAxiosData),
        fork(axiosSagas.postAxiosData),
        fork(axiosSagas.putAxiosData),
        fork(axiosSagas.deleteAxiosData),
        fork(axiosSagas.uploadAxiosData),
        fork(axiosSagas.resetUploadProgress),
    ])
}

export default rootSaga;