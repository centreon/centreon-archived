import { lazy, Suspense } from 'react';

import { not } from 'ramda';

import makeStyles from '@mui/styles/makeStyles';

import { LoadingSkeleton } from '@centreon/ui';

import PageLoader from '../components/PageLoader';
import { headerHeight } from '../Header';

import useApp from './useApp';

const useStyles = makeStyles((theme) => ({
  content: {
    display: 'flex',
    flexDirection: 'column',
    height: '100%',
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
    height: `calc(100vh - ${theme.spacing(headerHeight)})`,
    width: '100%',
  },
  wrapper: {
    alignItems: 'stretch',
    backgroundColor: theme.palette.background.paper,
    display: 'flex',
    fontFamily: theme.typography.fontFamily,
    height: '100%',
    overflow: 'hidden',
  },
}));

const MainRouter = lazy(() => import('../components/mainRouter'));
const Header = lazy(() => import('../Header'));
const Navigation = lazy(() => import('../Navigation'));

const App = (): JSX.Element => {
  const classes = useStyles();
  const { hasMinArgument } = useApp();

  const min = hasMinArgument();

  return (
    <Suspense fallback={<PageLoader />}>
      <div className={classes.wrapper}>
        {not(min) && (
          <Suspense fallback={<LoadingSkeleton height="100%" width={45} />}>
            <Navigation />
          </Suspense>
        )}
        <div className={classes.content} id="content">
          {not(min) && (
            <Suspense fallback={<LoadingSkeleton height={56} width="100%" />}>
              <Header />
            </Suspense>
          )}
          <div className={classes.mainContent}>
            <MainRouter />
          </div>
        </div>
      </div>
    </Suspense>
  );
};

export default App;
