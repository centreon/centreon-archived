import React, {Component} from "react";
import Header from "./components/header";
import {Switch} from "react-router-dom";
import {ConnectedRouter} from "react-router-redux";
import {history} from "./store";
import routes from "./route-maps/classicRoutes.js";
import ClassicRoute from "./components/router/classicRoute";
import NavigationComponent from "./components/navigation";
import Footer from "./components/footer";
import Fullscreen from 'react-fullscreen-crossbrowser';
import queryString from 'query-string';

class App extends Component {
  constructor(props) {
    super(props);

    // check in arguments if min=1
    const { search } = history.location
    const parsedArguments = queryString.parse(search)
    this.min = (parsedArguments.min === "1")

    this.state = {
      isFullscreenEnabled: false
    };
  }

  goFull = () => {
    this.setState({ isFullscreenEnabled: true });
  }

  render() {
    return (
      <ConnectedRouter history={history}>
        <div class="wrapper">
          {!this.min && // do not display menu if min=1
            <NavigationComponent/>
          }
          <div id="content">
            {!this.min && // do not display header if min=1
              <Header/>
            }
            <div id="fullscreen-wrapper">
              <Fullscreen
                enabled={this.state.isFullscreenEnabled}
                onChange={isFullscreenEnabled => this.setState({isFullscreenEnabled})}>
                <div className="full-screenable-node">
                  <div className="main-content">
                    <Switch>
                      {routes.map(({ path, comp, ...rest }) => (
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
              </Fullscreen>
            </div>
            {!this.min && // do not display footer if min=1
              <Footer/>
            }
          </div>
          <span className="full-screen" onClick={this.goFull}></span>
        </div>
      </ConnectedRouter>
    );
  }
}

export default App;
