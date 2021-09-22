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

export const history = createBrowserHistory({
  basename: document.baseURI.replace(window.location.origin, ''),
});

const createAppStore = (initialState = {}) => {
  const middlewares = [
    routerMiddleware(history),
    thunk,
    batchDispatchMiddleware,
    sagaMiddleware,
  ];

  const composeEnhancers =
    typeof window === 'object' && window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__
      ? window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({})
      : compose;

  const enhancer = composeEnhancers(applyMiddleware(...middlewares));

  const store = createStore(createRootReducer(history), initialState, enhancer);

  sagaMiddleware.run(sagas);

  return store;
};

export default createAppStore;
