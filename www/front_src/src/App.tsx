/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable react/sort-comp */
/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable react/prop-types */
/* eslint-disable react/jsx-filename-extension */

import React, { Component, ReactNode } from 'react';
import { hot } from 'react-hot-loader/root';

import { connect } from 'react-redux';
import { ConnectedRouter } from 'connected-react-router';
import Fullscreen from 'react-fullscreen-crossbrowser';
import queryString from 'query-string';

import { ThemeProvider } from '@centreon/ui';

import { withStyles } from '@material-ui/core';

import Header from './components/header';
import { history } from './store';
import NavigationComponent from './components/navigation';
import Tooltip from './components/tooltip';
import Footer from './components/footer';
import MainRouter from './components/mainRouter';
import axios from './axios';
import { fetchExternalComponents } from './redux/actions/externalComponentsActions';

import footerStyles from './components/footer/footer.scss';

const styles = {
  wrapper: {
    display: 'flex',
    alignItems: 'stretch',
    height: '100%',
    overflow: 'hidden',
  },
  fullScreenWrapper: {
    width: '100%',
    height: '100%',
    overflow: 'hidden',
    flexGrow: 1,
  },
  mainContent: {
    height: '100%',
    width: '100%',
    backgroundcolor: 'white',
  },
  content: {
    display: 'flex',
    flexDirection: 'column',
    overflow: 'hidden',
    width: '100%',
    height: ' 100vh',
    transition: 'all 0.3s',
    position: 'relative',
  },
};

// Extends Window interface
declare global {
  interface Window {
    fullscreenSearch: string;
    fullscreenHash: string;
  }
}

interface Props {
  fetchExternalComponents: () => void;
  classes;
}

interface State {
  isFullscreenEnabled: boolean;
}

class App extends Component<Props, State> {
  public state = {
    isFullscreenEnabled: false,
  };

  private keepAliveTimeout: NodeJS.Timeout | null = null;

  // check in arguments if min=1
  private getMinArgument = (): boolean => {
    const { search } = history.location;
    const parsedArguments = queryString.parse(search);

    return parsedArguments.min === '1';
  };

  // enable fullscreen
  private goFull = (): void => {
    // set fullscreen url parameters
    // this will be used to init iframe src
    window.fullscreenSearch = window.location.search;
    window.fullscreenHash = window.location.hash;

    // enable fullscreen after 200ms
    setTimeout(() => {
      this.setState({ isFullscreenEnabled: true });
    }, 200);
  };

  // disable fullscreen
  private removeFullscreenParams = (): void => {
    if (history.location.pathname === '/main.php') {
      history.push({
        pathname: '/main.php',
        search: window.fullscreenSearch,
        hash: window.fullscreenHash,
      });
    }

    // remove fullscreen parameters to keep normal routing
    delete window.fullscreenSearch;
    delete window.fullscreenHash;
  };

  // keep alive (redirect to login page if session is expired)
  private keepAlive = (): void => {
    this.keepAliveTimeout = setTimeout(() => {
      axios('internal.php?object=centreon_keepalive&action=keepAlive')
        .get()
        .then(() => this.keepAlive())
        .catch((error) => {
          if (error.response && error.response.status === 401) {
            // redirect to login page
            window.location.href = 'index.php?disconnect=1';
          } else {
            // keepalive must be done cause it may failed due to temporary unavailability
            this.keepAlive();
          }
        });
    }, 15000);
  };

  public componentDidMount(): void {
    // 2 - fetch external components (pages, hooks...)
    this.props.fetchExternalComponents();

    this.keepAlive();
  }

  public render(): ReactNode {
    const min = this.getMinArgument();

    const { classes } = this.props;

    return (
      <ConnectedRouter history={history}>
        <ThemeProvider>
          <div className={classes.wrapper}>
            {!min && <NavigationComponent />}
            <Tooltip />
            <div id="content" className={classes.content}>
              {!min && <Header />}
              <div
                id="fullscreen-wrapper"
                className={classes.fullScreenWrapper}
              >
                <Fullscreen
                  enabled={this.state.isFullscreenEnabled}
                  onClose={this.removeFullscreenParams}
                  onChange={(isFullscreenEnabled): void => {
                    this.setState({ isFullscreenEnabled });
                  }}
                >
                  <div className={classes.mainContent}>
                    <MainRouter />
                  </div>
                </Fullscreen>
              </div>
              {!min && <Footer />}
            </div>
            <span
              className={footerStyles['full-screen']}
              onClick={this.goFull}
            />
          </div>
        </ThemeProvider>
      </ConnectedRouter>
    );
  }
}

interface DispatchProps {
  fetchExternalComponents: () => void;
}

const mapDispatchToProps = (dispatch: (any) => void): DispatchProps => {
  return {
    fetchExternalComponents: (): void => {
      dispatch(fetchExternalComponents());
    },
  };
};

export default hot(connect(null, mapDispatchToProps)(withStyles(styles)(App)));
