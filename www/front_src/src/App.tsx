import * as React from 'react';

import { connect } from 'react-redux';
import { BrowserRouter as Router, useSearchParams } from 'react-router-dom';
import Fullscreen from 'react-fullscreen-crossbrowser';
import { Dispatch } from 'redux';
import { equals, not, pathEq } from 'ramda';

import FullscreenIcon from '@material-ui/icons/Fullscreen';
import { makeStyles, Fab } from '@material-ui/core';

import { getData, useRequest } from '@centreon/ui';

import Header from './Header';
import Nagigation from './Navigation';
import Footer from './components/footer';
import { fetchExternalComponents } from './redux/actions/externalComponentsActions';
import PageLoader from './components/PageLoader';
import Provider from './Provider';

const MainRouter = React.lazy(() => import('./components/mainRouter'));

const useStyles = makeStyles({
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

interface Props {
  getExternalComponents: () => void;
}

const keepAliveEndpoint =
  './api/internal.php?object=centreon_keepalive&action=keepAlive';

const App = ({ getExternalComponents }: Props): JSX.Element => {
  const classes = useStyles();

  const [isFullscreenEnabled, setIsFullscreenEnabled] = React.useState(false);
  const keepAliveIntervalRef = React.useRef<NodeJS.Timer | null>(null);

  const { sendRequest: keepAliveRequest } = useRequest({
    request: getData,
  });

  const [searchParams] = useSearchParams();

  const hasMinArgument = (): boolean => equals(searchParams.get('min'), '1');

  const displayInFullScreen = (): void => {
    setIsFullscreenEnabled(true);
  };

  const removeFullscreen = (): void => {
    setIsFullscreenEnabled(false);
  };

  const keepAlive = (): void => {
    keepAliveRequest({
      endpoint: keepAliveEndpoint
    }).catch((error) => {
      if (not(pathEq(['response', 'status'], 401, error))) {
        return;
      }

      clearInterval(keepAliveIntervalRef.current as NodeJS.Timer);
      window.location.href = './index.php?disconnect=1';
    });
  };

  React.useEffect(() => {
    getExternalComponents();
    keepAlive();

    keepAliveIntervalRef.current = setInterval(keepAlive, 15000);
  }, []);

  const min = hasMinArgument();

  return (
    <React.Suspense fallback={<PageLoader />}>
      <div className={classes.wrapper}>
        {not(min) && <Nagigation />}
        <div className={classes.content} id="content">
          {not(min) && <Header />}
          <div className={classes.fullScreenWrapper} id="fullscreen-wrapper">
            <Fullscreen
              enabled={isFullscreenEnabled}
              onClose={removeFullscreen}
            >
              <div className={classes.mainContent}>
                <MainRouter />
              </div>
            </Fullscreen>
          </div>
          {!min && <Footer />}
        </div>
        <Fab
          className={classes.fullscreenButton}
          color="default"
          size="small"
          onClick={displayInFullScreen}
        >
          <FullscreenIcon />
        </Fab>
      </div>
    </React.Suspense>
  );
};

const mapDispatchToProps = (dispatch: Dispatch): Props => {
  return {
    getExternalComponents: (): void => {
      dispatch(fetchExternalComponents());
    },
  };
};

const CentreonApp = connect(null, mapDispatchToProps)(App);

export default (): JSX.Element => (
  <Provider>
    <Router basename="/centreon">
      <CentreonApp />
    </Router>
  </Provider>
);
