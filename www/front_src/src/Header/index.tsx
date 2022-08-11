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
    justifyContent: 'space-between',
    margin: `0 ${theme.spacing(4)} 0 ${theme.spacing(3)}`,
    width: '100%',
  },
  header: {
    alignItems: 'center',
    background: theme.palette.common.black,
    display: 'flex',
    height: theme.spacing(9),
    width: '100%',
  },
  leftContainer: {
    alignItems: 'center',
    display: 'flex',
    gap: theme.spacing(3),
    [theme.breakpoints.down(600)]: {
      gap: theme.spacing(1),
    },
  },
  userMenu: {
    display: 'flex',
    justifyContent: 'flex-end',
  },
  userMenuContainer: {
    alignItems: 'center',
    display: 'flex',
  },
}));

const Header = (): JSX.Element => {
  const classes = useStyles();
  const headerRef = useRef<HTMLElement>(null);

  return (
    <header className={classes.header} ref={headerRef}>
      <div className={classes.container}>
        <div className={classes.leftContainer}>
          <PollerMenu />
          <ServiceStatusCounter />
          <HostStatusCounter />
          <FederatedComponent path="/bam/header/topCounter" />
        </div>
        <div className={classes.userMenuContainer}>
          <UserMenu headerRef={headerRef} />
        </div>
      </div>
    </header>
  );
};

export default Header;
