/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable react/sort-comp */
/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable react/prop-types */
/* eslint-disable react/jsx-filename-extension */

import React, { Component, ReactNode, Suspense } from 'react';

import { connect } from 'react-redux';
import { ConnectedRouter } from 'connected-react-router';
import Fullscreen from 'react-fullscreen-crossbrowser';
import queryString from 'query-string';

import FullscreenIcon from '@material-ui/icons/Fullscreen';
import { withStyles, createStyles, Fab } from '@material-ui/core';

import { LoadingSkeleton, ThemeProvider } from '@centreon/ui';

import { history } from './store';
import axios from './axios';
import { fetchExternalComponents } from './redux/actions/externalComponentsActions';
import PageLoader from './components/PageLoader';
import Provider from './Provider';

const MainRouter = React.lazy(() => import('./components/mainRouter'));
const Header = React.lazy(() => import('./Header'));
const Navigation = React.lazy(() => import('./Navigation'));
const Footer = React.lazy(() => import('./Footer'));

const styles = createStyles({
  content: {
    display: 'flex',
    flexDirection: 'column',
    height: ' 100vh',
    overflow: 'hidden',
    position: 'relative',
    transition: 'all 0.3s',
    width: '100%',
  },
  fullScreenWrapper: {
    flexGrow: 1,
    height: '100%',
    overflow: 'hidden',
    width: '100%',
  },
  fullscreenButton: {
    bottom: '10px',
    position: 'absolute',
    right: '20px',
    zIndex: 1500,
  },
  mainContent: {
    backgroundcolor: 'white',
    height: '100%',
    width: '100%',
  },
  wrapper: {
    alignItems: 'stretch',
    display: 'flex',
    height: '100%',
    overflow: 'hidden',
  },
});

// Extends Window interface
declare global {
  interface Window {
    fullscreenHash: string | null;
    fullscreenSearch: string | null;
  }
}

interface Props {
  classes;
  fetchExternalComponents: () => void;
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
        hash: window.fullscreenHash,
        pathname: '/main.php',
        search: window.fullscreenSearch,
      });
    }

    // remove fullscreen parameters to keep normal routing
    window.fullscreenSearch = null;
    window.fullscreenHash = null;
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
      <Suspense fallback={<PageLoader />}>
        <ConnectedRouter history={history}>
          <ThemeProvider>
            <div className={classes.wrapper}>
              {!min && (
                <React.Suspense
                  fallback={<LoadingSkeleton height="100%" width={45} />}
                >
                  <Navigation />
                </React.Suspense>
              )}
              <div className={classes.content} id="content">
                {!min && (
                  <React.Suspense
                    fallback={<LoadingSkeleton height={56} width="100%" />}
                  >
                    <Header />
                  </React.Suspense>
                )}
                <div
                  className={classes.fullScreenWrapper}
                  id="fullscreen-wrapper"
                >
                  <Fullscreen
                    enabled={this.state.isFullscreenEnabled}
                    onChange={(isFullscreenEnabled): void => {
                      this.setState({ isFullscreenEnabled });
                    }}
                    onClose={this.removeFullscreenParams}
                  >
                    <div className={classes.mainContent}>
                      <MainRouter />
                    </div>
                  </Fullscreen>
                </div>
                {!min && (
                  <React.Suspense
                    fallback={<LoadingSkeleton height={30} width="100%" />}
                  >
                    <Footer />
                  </React.Suspense>
                )}
              </div>
              <Fab
                className={classes.fullscreenButton}
                color="default"
                size="small"
                onClick={this.goFull}
              >
                <FullscreenIcon />
              </Fab>
            </div>
          </ThemeProvider>
        </ConnectedRouter>
      </Suspense>
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

const CentreonApp = connect(null, mapDispatchToProps)(withStyles(styles)(App));

export default (): JSX.Element => (
  <Provider>
    <CentreonApp />
  </Provider>
);
