/* eslint-disable import/no-extraneous-dependencies */

import { createStore, applyMiddleware, compose } from 'redux';
import { routerMiddleware } from 'connected-react-router';
import { batchDispatchMiddleware } from 'redux-batched-actions';
import thunk from 'redux-thunk';
import createSagaMiddleware from 'redux-saga';
import { createBrowserHistory } from 'history';
import sagas from '../redux/sagas';
import createRootReducer from '../redux/reducers';

const sagaMiddleware = createSagaMiddleware();

const paths = window.location.pathname.split('/');
export const history = createBrowserHistory({
  basename: `/${paths[1] ? paths[1] : ''}`,
});

const createAppStore = (options, initialState = {}) => {
  const middlewares = [
    routerMiddleware(history),
    thunk,
    sagaMiddleware,
    batchDispatchMiddleware,
  ];

  const store = createStore(
    createRootReducer(history),
    initialState,
    compose(
      applyMiddleware(...middlewares),
      window.devToolsExtension ? window.devToolsExtension() : (f) => f,
    ),
  );

  sagaMiddleware.run(sagas);
  return store;
};

export default createAppStore;
