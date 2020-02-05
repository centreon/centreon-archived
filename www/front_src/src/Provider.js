/* eslint-disable react/jsx-filename-extension */

import React, { Component } from 'react';
import { Provider } from 'react-redux';
import App from './App';
import createStore from './store';
import setTranslations from './translations';

const store = createStore();

class AppProvider extends Component {
  state = {
    translationsLoaded: false,
  };

  componentDidMount = () => {
    setTranslations(store, this.finishLoading);
  };

  finishLoading = () => {
    this.setState({ translationsLoaded: true });
  };

  render() {
    const { translationsLoaded } = this.state;

    return (
      translationsLoaded && (
        <Provider store={store}>
          <App />
        </Provider>
      )
    );
  }
}

export default AppProvider;
