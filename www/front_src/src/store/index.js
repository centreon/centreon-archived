import { createStore, applyMiddleware, compose } from "redux";
import { routerMiddleware } from "react-router-redux";
import reducers from "../redux/reducers";

const createHistory =
  typeof document !== undefined
    ? require("history/createBrowserHistory").default
    : () => {};

export const history = createHistory();

const createAppStore = (options, initialState = {}) => {
  const middlewares = [routerMiddleware(history)];

  const store = createStore(
    reducers,
    initialState,
    compose(
      applyMiddleware(...middlewares),
      window.devToolsExtension ? window.devToolsExtension() : f => f
    )
  );

  return store;
};

export default createAppStore;
