import { all, fork } from 'redux-saga/effects';

import * as axiosSagas from './axios';
import * as bamSagas from './bam';

const rootSaga = function* rootSaga(){
    yield all([
        fork(axiosSagas.getAxiosData),
        fork(axiosSagas.postAxiosData),
        fork(axiosSagas.putAxiosData),
        fork(axiosSagas.deleteAxiosData),
        fork(axiosSagas.uploadAxiosData),
        fork(axiosSagas.resetUploadProgress),
        fork(bamSagas.setBaConfiguration),
        fork(bamSagas.setBaConfigurationErrors)
    ])
}

export default rootSaga;
