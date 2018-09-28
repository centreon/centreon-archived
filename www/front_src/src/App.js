import React, {Component} from "react";
import Header from "./components/header";
import {Switch, BrowserRouter} from "react-router-dom";
import {ConnectedRouter} from "react-router-redux";
import {history} from "./store";
import routes from "./route-maps/classicRoutes.js";
import ClassicRoute from "./components/router/classicRoute";
import NavigationComponent from "./components/navigation";
import "babel-polyfill";
import Footer from "./components/footer";
import Fullscreen from 'react-fullscreen-crossbrowser';

class App extends Component {
  
  constructor(props) {
    super();
    this.state = {
      isFullscreenEnabled: false,
    };
  }

  goFull = () => {
    this.setState({ isFullscreenEnabled: true });
  }

  render() {
    return (
      <ConnectedRouter history={history}>
        <div class="wrapper">
          <NavigationComponent/>
          <div id="content">
            <Header/>
            <Fullscreen
              enabled={this.state.isFullscreenEnabled}
              onChange={isFullscreenEnabled => this.setState({isFullscreenEnabled})}
            >
              <div className='full-screenable-node'>
                <div class="main-content">
                  <Switch onChange={this.handle}>
                    {routes.map(({
                      path,
                      comp,
                      ...rest
                    }, i) => (<ClassicRoute history={history} path={path} component={comp} {...rest}/>))}
                  </Switch>
                </div>
              </div>
            </Fullscreen>
            <Footer/>
          </div>
          <span className="full-screen" onClick={this.goFull}></span>
        </div>
      </ConnectedRouter>
    );
  }
}

export default App;
