import { createStore, applyMiddleware, compose } from "redux";
import { routerMiddleware } from "react-router-redux";
import reducers from "../redux/reducers";
import thunk from 'redux-thunk';
import createSagaMiddleware from 'redux-saga';
import sagas from '../redux/sagas';

const sagaMiddleware = createSagaMiddleware();

const createHistory =
  typeof document !== undefined
    ? require("history/createBrowserHistory").default
    : () => {};

export const history = createHistory();

const createAppStore = (options, initialState = {}) => {
  const middlewares = [
    routerMiddleware(history), 
    thunk,
    sagaMiddleware,
  ];

  const store = createStore(
    reducers,
    initialState,
    compose(
      applyMiddleware(...middlewares),
      window.devToolsExtension ? window.devToolsExtension() : f => f
    )
  );

  sagaMiddleware.run(sagas);
  return store;
};

export default createAppStore;