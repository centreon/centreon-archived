import * as React from 'react';

import { makeStyles } from '@mui/styles';

import Hook from '../components/Hook';

import PollerMenu from './PollerMenu';
import HostStatusCounter from './RessourceStatusCounter/Host';
import ServiceStatusCounter from './RessourceStatusCounter/Service';
import UserMenu from './userMenu';

const HookComponent = Hook as unknown as (props) => JSX.Element;

const useStyles = makeStyles({
  container: {
    alignItems: 'center',
    display: 'flex',
    justifyContent: 'space-between',
  },
  header: {
    background: '#232f39',
  },
  rightContainer: {
    display: 'flex',
  },
});

const Header = (): JSX.Element => {
  const classes = useStyles();

  return (
    <header className={classes.header}>
      <div className={classes.container}>
        <div>
          <PollerMenu />
        </div>
        <div className={classes.rightContainer}>
          <HookComponent path="/header/topCounter" />
          <HostStatusCounter />
          <ServiceStatusCounter />
          <UserMenu />
        </div>
      </div>
    </header>
  );
};

export default Header;
