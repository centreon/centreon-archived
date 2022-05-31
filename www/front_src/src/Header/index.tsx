import { makeStyles } from '@mui/styles';

import Hook from '../components/Hook';

import PollerMenu from './PollerMenu';
import HostStatusCounter from './RessourceStatusCounter/Host';
import ServiceStatusCounter from './RessourceStatusCounter/Service';
import UserMenu from './userMenu';
import SwitchMode from './SwitchThemeMode';

const HookComponent = Hook as unknown as (props) => JSX.Element;

const useStyles = makeStyles((theme) => ({
  container: {
    alignItems: 'center',
    display: 'flex',
  },
  header: {
    background: theme.palette.common.black,
    width: '100%',
  },
  hostStatusContainer: {
    display: 'flex',
    flex: 1,
    justifyContent: 'flex-end',
  },
  pollerContainer: {
    display: 'flex',
    flex: 0.75,
  },
  rightContainer: {
    display: 'flex',
    flex: 3,
  },
  serviceStatusContainer: {
    display: 'flex',
    flex: 1,
  },
  switchModeContainer: {
    display: 'flex',
    flex: 0.25,
    justifyContent: 'flex-end',
  },
  userMenuContainer: {
    display: 'flex',
    flex: 0.35,
    justifyContent: 'flex-end',
  },
}));

const Header = (): JSX.Element => {
  const classes = useStyles();

  return (
    <header className={classes.header}>
      <div className={classes.container}>
        <div className={classes.pollerContainer}>
          <PollerMenu />
        </div>
        <div className={classes.rightContainer}>
          <HookComponent path="/header/topCounter" />
          <div className={classes.hostStatusContainer}>
            <HostStatusCounter />
          </div>
          <div className={classes.serviceStatusContainer}>
            <ServiceStatusCounter />
          </div>
          <div className={classes.switchModeContainer}>
            <SwitchMode />
          </div>
          <div className={classes.userMenuContainer}>
            <UserMenu />
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;
