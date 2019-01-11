import {all,fork} from 'redux-saga/effects';

import * as axiosSagas from './axios';

const rootSaga = function* rootSaga(){
    yield all([
        fork(axiosSagas.getAxiosData)
    ])
}

export default rootSaga;