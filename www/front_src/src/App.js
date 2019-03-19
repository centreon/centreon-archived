import React, { Component } from "react";
import config from "./config";
import Header from "./components/header";
import { Switch } from "react-router-dom";
import { connect } from "react-redux";
import { ConnectedRouter } from "react-router-redux";
import { history } from "./store";
import ClassicRoute from "./components/router/classicRoute";
import ReactRoute from './components/router/reactRoute';
import ExternalRouter from "./components/externalRouter";

import { classicRoutes, reactRoutes } from "./route-maps";
import NavigationComponent from "./components/navigation";
import Tooltip from "./components/tooltip";
import Footer from "./components/footer";
import Fullscreen from 'react-fullscreen-crossbrowser';
import queryString from 'query-string';
import axios from './axios';
import NotAllowedPage from './route-components/notAllowedPage';

import { setExternalComponents } from "./redux/actions/externalComponentsActions";

class App extends Component {

  state = {
    isFullscreenEnabled: false,
    acls: [],
    aclsLoaded: false,
    reactRouter: null
  }

  keepAliveTimeout = null

  // check in arguments if min=1
  getMinArgument = () => {
    const { search } = history.location
    const parsedArguments = queryString.parse(search)

    return (parsedArguments.min === "1")
  }

  // enable fullscreen
  goFull = () => {
    // set fullscreen url parameters
    // this will be used to init iframe src
    window['fullscreenSearch'] = window.location.search
    window['fullscreenHash'] = window.location.hash

    // enable fullscreen after 200ms
    setTimeout(() => {
      this.setState({ isFullscreenEnabled: true });
    }, 200)
  }

  // disable fullscreen
  removeFullscreenParams = () => {
    if (history.location.pathname == './main.php') {
      history.push({
        pathname: './main.php',
        search: window['fullscreenSearch'],
        hash: window['fullscreenHash']
      })
    }

    // remove fullscreen parameters to keep normal routing
    delete window['fullscreenSearch'];
    delete window['fullscreenHash'];
  }

  // get allowed routes
  getAcl = () => {
    axios("internal.php?object=centreon_acl_webservice&action=getCurrentAcl")
      .get()
      .then(({data}) => {
        this.setState(
          {acls: data, aclsLoaded: true},
          () => { this.getReactRoutes(); }
        );
      });
  }

  // get external components (pages, hooks...)
  getExternalComponents = () => {
    const { setExternalComponents } = this.props;

    axios("internal.php?object=centreon_frontend_component&action=components")
      .get()
      .then(({ data }) => {
        // store external components in redux
        setExternalComponents(data);
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
          } else {
            // keepalive must be done cause it may failed due to temporary unavailability
            this.keepAlive();
          }
        })
    }, 15000)
  }

  componentDidMount() {
    this.getAcl();
    this.getExternalComponents();
    this.keepAlive();
  }

  getReactRoutes = () => {
    const { acls } = this.state;
    let reactRouter = reactRoutes.map(({ path, comp, ...rest }) => (
      <ReactRoute
        history={history}
        path={path}
        component={acls.includes(path) ? comp : NotAllowedPage}
        {...rest}
      />
    ));
    this.setState({ reactRouter });
  }

  render() {
    const { reactRouter } = this.state;

    const min = this.getMinArgument();

    return (
      <ConnectedRouter history={history}>
        <div class="wrapper">
          {!min && // do not display menu if min=1
            <NavigationComponent/>
          }
          <Tooltip/>
          <div id="content">
            {!min && // do not display header if min=1
              <Header/>
            }
            <div id="fullscreen-wrapper">
              <Fullscreen
                enabled={this.state.isFullscreenEnabled}
                onClose={this.removeFullscreenParams}
                onChange={isFullscreenEnabled => this.setState({isFullscreenEnabled})}>
                <div className="full-screenable-node">
                  <div className="main-content">
                    <Switch>
                      {classicRoutes.map(({path, comp, ...rest}, i) => (
                        <ClassicRoute key={i} history={history} path={path} component={comp} {...rest} />
                      ))}
                      {reactRouter}
                      <ExternalRouter/>
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

const mapDispatchToProps = {
  setExternalComponents
};

export default connect(null, mapDispatchToProps)(App);