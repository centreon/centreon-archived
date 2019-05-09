import React, { Component } from "react";
import Header from "./components/header";
import { Switch, Route } from "react-router-dom";
import { ConnectedRouter } from "react-router-redux";
import { history } from "./store";
import ReactRouter from "./components/reactRouter";
import LegacyRoute from "./route-components/legacyRoute";

import NavigationComponent from "./components/navigation";
import Tooltip from "./components/tooltip";
import Footer from "./components/footer";
import Fullscreen from 'react-fullscreen-crossbrowser';
import queryString from 'query-string';
import axios from './axios';

import styles from './App.scss';
import footerStyles from './components/footer/footer.scss';
import contentStyles from './styles/partials/_content.scss';

class App extends Component {

  state = {
    isFullscreenEnabled: false
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
    if (history.location.pathname == '/main.php') {
      history.push({
        pathname: '/main.php',
        search: window['fullscreenSearch'],
        hash: window['fullscreenHash']
      })
    }

    // remove fullscreen parameters to keep normal routing
    delete window['fullscreenSearch'];
    delete window['fullscreenHash'];
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
            window.location.href = 'index.php?disconnect=1'
          } else {
            // keepalive must be done cause it may failed due to temporary unavailability
            this.keepAlive();
          }
        })
    }, 15000)
  }

  componentDidMount() {
    this.keepAlive();
  }

  render() {
    const min = this.getMinArgument();

    return (
      <ConnectedRouter history={history}>
        <div className={styles["wrapper"]}>
          {!min && // do not display menu if min=1
            <NavigationComponent/>
          }
          <Tooltip/>
          <div id="content" className={contentStyles['content']}>
            {!min && // do not display header if min=1
              <Header/>
            }
            <div id="fullscreen-wrapper" className={contentStyles['fullscreen-wrapper']}>
              <Fullscreen
                enabled={this.state.isFullscreenEnabled}
                onClose={this.removeFullscreenParams}
                onChange={isFullscreenEnabled => this.setState({isFullscreenEnabled})}
              >
                <div className={styles["main-content"]}>
                  <Switch>
                    <Route path="/main.php" exact component={LegacyRoute}/>
                    <Route path="/" exact render={() => (<Redirect to="/main.php"/>)}/>
                    <Route path="/" component={ReactRouter}/>
                  </Switch>
                </div>
              </Fullscreen>
            </div>
            {!min && // do not display footer if min=1
              <Footer/>
            }
          </div>
          <span className={footerStyles["full-screen"]} onClick={this.goFull} />
        </div>
      </ConnectedRouter>
    );
  }
}

export default App;
