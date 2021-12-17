import * as React from 'react';

import { Provider as ReduxProvider } from 'react-redux';
import Fullscreen from 'react-fullscreen-crossbrowser';
import { not } from 'ramda';

import FullscreenIcon from '@material-ui/icons/Fullscreen';
import { makeStyles, Fab } from '@material-ui/core';

import PageLoader from '../components/PageLoader';
import createStore from '../store';
import Header from '../Header';
import Navigation from '../Navigation';
import Footer from '../Footer';

import useApp from './useApp';

const store = createStore();

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

const MainRouter = React.lazy(() => import('../components/mainRouter'));

const App = (): JSX.Element => {
  const classes = useStyles();
  const {
    dataLoaded,
    hasMinArgument,
    isFullscreenEnabled,
    displayInFullScreen,
    removeFullscreen,
  } = useApp();

  if (!dataLoaded) {
    return <PageLoader />;
  }

  const min = hasMinArgument();

  return (
    <ReduxProvider store={store}>
      <React.Suspense fallback={<PageLoader />}>
        <div className={classes.wrapper}>
          {not(min) && <Navigation />}
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
    </ReduxProvider>
  );
};

export default App;
