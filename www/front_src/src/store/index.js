/* eslint-disable import/no-extraneous-dependencies */

import { createStore, applyMiddleware, compose } from 'redux';
import { batchDispatchMiddleware } from 'redux-batched-actions';
import thunk from 'redux-thunk';

import createRootReducer from '../redux/reducers';

const createAppStore = (initialState = {}) => {
  const middlewares = [thunk, batchDispatchMiddleware];

  const composeEnhancers =
    typeof window === 'object' && window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__
      ? window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({})
      : compose;

  const enhancer = composeEnhancers(applyMiddleware(...middlewares));

  const store = createStore(createRootReducer(), initialState, enhancer);

  return store;
};

export default createAppStore;
