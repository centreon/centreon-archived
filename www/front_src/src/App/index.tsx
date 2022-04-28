import * as React from 'react';

import { Provider as ReduxProvider } from 'react-redux';
import { not } from 'ramda';

import makeStyles from '@mui/styles/makeStyles';

import { LoadingSkeleton } from '@centreon/ui';

import PageLoader from '../components/PageLoader';
import createStore from '../store';

import useApp from './useApp';

const store = createStore();

const useStyles = makeStyles((theme) => ({
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
    backgroundColor: theme.palette.background.default,
    height: '100%',
    width: '100%',
  },
  wrapper: {
    alignItems: 'stretch',
    display: 'flex',
    height: '100%',
    overflow: 'hidden',
  },
}));

const MainRouter = React.lazy(() => import('../components/mainRouter'));
const Header = React.lazy(() => import('../Header'));
const Navigation = React.lazy(() => import('../Navigation'));

const App = (): JSX.Element => {
  const classes = useStyles();
  const { dataLoaded, hasMinArgument } = useApp();

  if (!dataLoaded) {
    return <PageLoader />;
  }

  const min = hasMinArgument();

  return (
    <ReduxProvider store={store}>
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
            {not(min) && (
              <React.Suspense
                fallback={<LoadingSkeleton height={56} width="100%" />}
              >
                <Header />
              </React.Suspense>
            )}
            <div className={classes.fullScreenWrapper} id="fullscreen-wrapper">
              <div className={classes.mainContent}>
                <MainRouter />
              </div>
            </div>
          </div>
        </div>
      </React.Suspense>
    </ReduxProvider>
  );
};

export default App;
