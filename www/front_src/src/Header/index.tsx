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
  hookComponent: {
    display: 'flex',
    flex: 0.4,
    justifyContent: 'flex-end',
  },
  hostStatusContainer: {
    display: 'flex',
    flex: 0.35,
    justifyContent: 'center',
  },
  pollerContainer: {
    flex: 0.5,
  },
  rightContainer: {
    alignItems: 'center',
    display: 'flex',
    flex: 1.1,
  },
  serviceStatusContainer: {
    display: 'flex',
    flex: 0.45,
  },
  userMenu: {
    display: 'flex',
    flex: 0.8,
    justifyContent: 'flex-end',
  },
  userMenuContainer: {
    alignItems: 'center',
    display: 'flex',
    flex: 0.4,
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
          <div className={classes.hookComponent}>
            <HookComponent path="/header/topCounter" />
          </div>
          <div className={classes.hostStatusContainer}>
            <HostStatusCounter />
          </div>
          <div className={classes.serviceStatusContainer}>
            <ServiceStatusCounter />
          </div>
          <div className={classes.userMenuContainer}>
            <SwitchMode />
            <div className={classes.userMenu}>
              <UserMenu />
            </div>
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;
