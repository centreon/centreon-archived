/* eslint-disable react/jsx-filename-extension */

import React, { Component } from 'react';
import { Provider } from 'react-redux';
import App from './App';
import createStore from './store';
import setTranslations from './translations';

const store = createStore();

interface State {
  translationsLoaded: boolean;
}

class AppProvider extends Component<State> {
  public state = {
    translationsLoaded: false,
  };

  public componentDidMount = () => {
    setTranslations(store, this.finishLoading);
  };

  private finishLoading = () => {
    this.setState({ translationsLoaded: true });
  };

  public render() {
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
