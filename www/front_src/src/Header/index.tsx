import { useRef } from 'react';

import { makeStyles } from '@mui/styles';

import FederatedComponent from '../components/FederatedComponents';

import PollerMenu from './PollerMenu';
import HostStatusCounter from './RessourceStatusCounter/Host';
import ServiceStatusCounter from './RessourceStatusCounter/Service';
import UserMenu from './userMenu';

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
    flex: 0.4,
  },
  rightContainer: {
    alignItems: 'center',
    display: 'flex',
    flex: 0.9,
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
    flex: 0.3,
    justifyContent: 'flex-end',
  },
}));

const Header = (): JSX.Element => {
  const classes = useStyles();
  const headerRef = useRef<HTMLElement>(null);

  return (
    <header className={classes.header} ref={headerRef}>
      <div>testooo</div>
      <div className={classes.container}>
        <div className={classes.pollerContainer}>
          <PollerMenu />
        </div>
        <div className={classes.rightContainer}>
          <div className={classes.hookComponent}>
            <FederatedComponent path="/bam/header/topCounter" />
          </div>
          <div className={classes.hostStatusContainer}>
            <HostStatusCounter />
          </div>
          <div className={classes.serviceStatusContainer}>
            <ServiceStatusCounter />
          </div>
          <div className={classes.userMenuContainer}>
            <UserMenu headerRef={headerRef} />
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;
