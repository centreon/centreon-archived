import React, { Component } from "react";
import Header from "./components/header";
import { Switch, BrowserRouter } from "react-router-dom";
import { ConnectedRouter } from "react-router-redux";
import { history } from "./store";
import routes from "./route-maps/classicRoutes.js";
import ClassicRoute from "./components/router/classicRoute";
import NavigationComponent from "./components/navigation";

class App extends Component {
  render() {
    return (
      <ConnectedRouter history={history}>
        <div class="wrapper">
          <NavigationComponent />
          <div id="content">
            <Header />
            <div class="main-content">
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
          </div>
        </div>
      </ConnectedRouter>
    );
  }
}

export default App;
