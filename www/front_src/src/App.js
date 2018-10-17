import React, {Component} from "react";
import config from "./config";
import Header from "./components/header";
import {Switch} from "react-router-dom";
import {ConnectedRouter} from "react-router-redux";
import {history} from "./store";
import ClassicRoute from "./components/router/classicRoute";
import ReactRoute from './components/router/reactRoute';

import {classicRoutes, reactRoutes} from "./route-maps";
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
    aclsLoaded: false,
    refreshIntervals: {},
    intervalsLoaded: false
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

  getRefreshIntervals = () => {
    axios("internal.php?object=centreon_topcounter&action=refreshIntervals")
      .get()
      .then(({data}) => {
        this.setState({refreshIntervals: data, intervalsLoaded: true});
      })
      .catch((err) => {
        console.log(err);
      });
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
    this.getAcl();
    this.keepAlive();
  }

  componentDidMount = () => {
    this.getRefreshIntervals();
  }

  linkReactRoutesAndComponents = () => {
    const {acls} = this.state;
    return reactRoutes.map(({ path, comp, ...rest }) => (
      <ReactRoute
        history={history}
        path={path}
        component={acls.includes(`/${path.split('/_CENTREON_PATH_PLACEHOLDER_/')[1]}`) ? comp : NotAllowedPage}
        {...rest}
      />
    ))
  }

  render() {
    const {aclsLoaded} = this.state;
    const min = this.getMinArgument();
    const {refreshIntervals} = this.state;

    let reactRouter = '';

    if (aclsLoaded) {
      reactRouter = this.linkReactRoutesAndComponents();
    }

    return (
      <ConnectedRouter history={history}>
        <div class="wrapper">
          {!min && // do not display menu if min=1
            <NavigationComponent/>
          }
          <div id="content">
            {!min && // do not display header if min=1
              <Header refreshIntervals={refreshIntervals}/>
            }
            <div id="fullscreen-wrapper">
              <Fullscreen
                enabled={this.state.isFullscreenEnabled}
                onChange={isFullscreenEnabled => this.setState({isFullscreenEnabled})}>
                <div className="full-screenable-node">
                  <div className="main-content">
                    <Switch>
                      {classicRoutes.map(({path, comp, ...rest}, i) => (
                        <ClassicRoute key={i} history={history} path={path} component={comp} {...rest} />
                      ))}
                      {aclsLoaded && reactRouter}
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