import React, {Component} from "react";
import Header from "./components/header";
import {Switch} from "react-router-dom";
import {ConnectedRouter} from "react-router-redux";
import {history} from "./store";
import routes from "./route-maps/classicRoutes.js";
import ClassicRoute from "./components/router/classicRoute";
import NavigationComponent from "./components/navigation";
import Footer from "./components/footer";
import Fullscreen from 'react-full-screen';

class App extends Component {

  constructor(props) {
    super(props);
    this.state = {
      isFull: false
    };
  }

  goFull = () => {
    this.setState({isFull: true});
  }

  render() {
    return (
      <ConnectedRouter history={history}>
        <div class="wrapper">
          <NavigationComponent/>
          <div id="content">
            <Header/>
            <div id="fullscreen-wrapper">
              <Fullscreen
                enabled={this.state.isFull}
                onChange={isFull => this.setState({isFull})}>
                <div className="full-screenable-node">
                  <div class="main-content">
                    <Switch onChange={this.handle}>
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
            <Footer/>
          </div>
          <span className="full-screen" onClick={this.goFull}></span>
        </div>
      </ConnectedRouter>
    );
  }
}

export default App;
