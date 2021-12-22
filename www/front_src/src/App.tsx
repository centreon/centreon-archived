import * as React from 'react';

import { BrowserRouter as Router, useSearchParams } from 'react-router-dom';
import Fullscreen from 'react-fullscreen-crossbrowser';
import { equals, not, pathEq } from 'ramda';

import FullscreenIcon from '@material-ui/icons/Fullscreen';
import { makeStyles, Fab } from '@material-ui/core';

import { getData, LoadingSkeleton, useRequest } from '@centreon/ui';

import PageLoader from './components/PageLoader';
import Provider from './Provider';

const MainRouter = React.lazy(() => import('./components/mainRouter'));
const Header = React.lazy(() => import('./Header'));
const Navigation = React.lazy(() => import('./Navigation'));
const Footer = React.lazy(() => import('./Footer'));

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

const keepAliveEndpoint =
  './api/internal.php?object=centreon_keepalive&action=keepAlive';

const App = (): JSX.Element => {
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
      endpoint: keepAliveEndpoint,
    }).catch((error) => {
      if (not(pathEq(['response', 'status'], 401, error))) {
        return;
      }

      clearInterval(keepAliveIntervalRef.current as NodeJS.Timer);
      window.location.href = './index.php?disconnect=1';
    });
  };

  React.useEffect(() => {
    keepAlive();

    keepAliveIntervalRef.current = setInterval(keepAlive, 15000);
  }, []);

  const min = hasMinArgument();

  return (
    <React.Suspense fallback={<PageLoader />}>
      <div className={classes.wrapper}>
        {not(min) && (
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
          onClick={displayInFullScreen}
        >
          <FullscreenIcon />
        </Fab>
      </div>
    </React.Suspense>
  );
};

export default (): JSX.Element => {
  const basename =
    (document.getElementsByTagName('base')[0].getAttribute('href') as string) ||
    '';

  return (
    <Provider>
      <Router basename={basename}>
        <App />
      </Router>
    </Provider>
  );
};
