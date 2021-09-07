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

import { withStyles, createStyles } from '@material-ui/core';

import { ThemeProvider } from '@centreon/ui';

import Header from './components/header';
import { history } from './store';
import Nagigation from './Navigation';
import Tooltip from './components/tooltip';
import Footer from './components/footer';
import axios from './axios';
import { fetchExternalComponents } from './redux/actions/externalComponentsActions';
import footerStyles from './components/footer/footer.scss';
import PageLoader from './components/PageLoader';

const MainRouter = React.lazy(() => import('./components/mainRouter'));

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
              {!min && <Nagigation />}
              <Tooltip />
              <div className={classes.content} id="content">
                {!min && <Header />}
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
                {!min && <Footer />}
              </div>
              <span
                className={footerStyles['full-screen']}
                onClick={this.goFull}
              />
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

export default connect(null, mapDispatchToProps)(withStyles(styles)(App));
