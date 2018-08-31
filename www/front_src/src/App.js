import React, { Component } from "react";
import Header from "./components/header";
import { Switch, BrowserRouter } from "react-router-dom";
import { ConnectedRouter } from "react-router-redux";
import { history } from "./store";
import routes from "./route-maps/classicRoutes.js";
import ClassicRoute from "./components/router/classicRoute";

class App extends Component {
  render() {
    return (
      <ConnectedRouter history={history}>
        <div>
          <div>
            <Header />
          </div>
          <Switch onChange={this.handle}>
            {routes.map(({ path, comp, ...rest }, i) => (
              <ClassicRoute
                history={history}
                path={path} 
                component={comp}
                {...rest}
              />
            ))}
          </Switch>
        </div>
      </ConnectedRouter>
    );
  }
}

export default App;
