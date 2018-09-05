import React, { Component } from "react";
import { Provider } from "react-redux";
import App from "./App";
import createStore from "./store";

const store = createStore();

class AppProvider extends Component {
  render = () => {
    return (
      <Provider store={store}>
        <App />
      </Provider>
    );
  };
}

export default AppProvider;
