import { createStore, applyMiddleware, compose } from "redux";
import { routerMiddleware } from "react-router-redux";
import reducers from "../redux/reducers";
import thunk from 'redux-thunk';
import { loadTranslations, setLocale, syncTranslationWithStore } from 'react-redux-i18n';

const translationsObject = {
  en: {
    application: {
      title: 'Awesome app with i18n!',
      hello: 'Hello, %{name}!'
    },
    date: {
      long: 'MMMM Do, YYYY'
    },
    export: 'Export %{count} items',
    export_0: 'Nothing to export',
    export_1: 'Export %{count} item',
    two_lines: 'Line 1<br />Line 2',
    literal_two_lines: 'Line 1\
Line 2'
  },
  nl: {
    application: {
      title: 'Toffe app met i18n!',
      hello: 'Hallo, %{name}!'
    },
    date: {
      long: 'D MMMM YYYY'
    },
    export: 'Exporteer %{count} dingen',
    export_0: 'Niks te exporteren',
    export_1: 'Exporteer %{count} ding',
    two_lines: 'Regel 1<br />Regel 2',
    literal_two_lines: 'Regel 1\
Regel 2'
  }
};

const createHistory =
  typeof document !== undefined
    ? require("history/createBrowserHistory").default
    : () => {};

export const history = createHistory();

const createAppStore = (options, initialState = {}) => {
  const middlewares = [
    routerMiddleware(history), 
    thunk,
  ];

  const store = createStore(
    reducers,
    initialState,
    compose(
      applyMiddleware(...middlewares),
      window.devToolsExtension ? window.devToolsExtension() : f => f
    )
  );

  syncTranslationWithStore(store)
  store.dispatch(loadTranslations(translationsObject));
  store.dispatch(setLocale('en'));

  return store;
};

export default createAppStore;
