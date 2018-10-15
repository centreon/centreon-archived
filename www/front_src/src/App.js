import React, {Component} from "react";
import config from "./config";
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
import axios from './axios';
import NotAllowedPage from './route-components/notAllowedPage';

class App extends Component {

  state = {
    isFullscreenEnabled: false,
    acls: [],
    aclsLoaded: false
  }

  keepAliveTimeout = null

  // check in arguments if min=1
  getMinArgument = () => {
    const { search } = history.location
    const parsedArguments = queryString.parse(search)

    return (parsedArguments.min === "1")
  }

  goFull = () => {
    this.setState({ isFullscreenEnabled: true });
  }

  // get allowed routes
  getAcl = () => {
    axios("internal.php?object=centreon_acl_webservice&action=getCurrentAcl")
      .get()
      .then(({data}) => this.setState({acls: data, aclsLoaded: true}))
  }

  // keep alive (redirect to login page if session is expired)
  keepAlive = () => {
    this.keepAliveTimeout = setTimeout(() => {
      axios("internal.php?object=centreon_keepalive&action=keepAlive")
        .get()
        .then(() => this.keepAlive())
        .catch(error => {
          if (error.response.status == 401) {
            // redirect to login page
            window.location.href = config.urlBase + 'index.php?disconnect=1'
          }
        })
    }, 15000)
  }

  UNSAFE_componentWillMount = () => {
    this.getAcl()
    this.keepAlive()
  }

  linkRoutesAndComponents = () => {
    const {acls} = this.state;
    return routes.map(({ path, comp, ...rest }) => (
      <ClassicRoute
        history={history}
        path={path}
        component={acls.includes(`/${path.split('/_CENTREON_PATH_PLACEHOLDER_/')[1]}`) ? NotAllowedPage : comp}
        {...rest}
      />
    ))
  }

  render() {
    const {aclsLoaded} = this.state;
    const min = this.getMinArgument();
    let router = '';

    if (aclsLoaded) {
      router = this.linkRoutesAndComponents();
    }

    return (
      aclsLoaded && <ConnectedRouter history={history}>
        <div class="wrapper">
          {!min && // do not display menu if min=1
            <NavigationComponent/>
          }
          <div id="content">
            {!min && // do not display header if min=1
              <Header/>
            }
            <div id="fullscreen-wrapper">
              <Fullscreen
                enabled={this.state.isFullscreenEnabled}
                onChange={isFullscreenEnabled => this.setState({isFullscreenEnabled})}>
                <div className="full-screenable-node">
                  <div className="main-content">
                    <Switch>
                      {router}
                    </Switch>
                  </div>
                </div>
              </Fullscreen>
            </div>
            {!min && // do not display footer if min=1
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