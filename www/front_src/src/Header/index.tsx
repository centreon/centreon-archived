import { useRef } from 'react';

import { equals } from 'ramda';

import { makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

import { ThemeMode } from '@centreon/ui-context';

import FederatedComponent from '../components/FederatedComponents';

import PollerMenu from './PollerMenu';
import HostStatusCounter from './RessourceStatusCounter/Host';
import ServiceStatusCounter from './RessourceStatusCounter/Service';
import UserMenu from './userMenu';

export const isDarkMode = (theme: Theme): boolean =>
  equals(theme.palette.mode, ThemeMode.dark);

export const headerHeight = 7;

const useStyles = makeStyles((theme) => ({
  header: {
    alignItems: 'center',
    backgroundColor: isDarkMode(theme)
      ? theme.palette.common.black
      : theme.palette.primary.dark,
    display: 'flex',
    height: theme.spacing(headerHeight),
    padding: `0 ${theme.spacing(3)}`,
  },
  item: {
    flex: '1 0 120px',
  },
  leftContainer: {
    alignItems: 'center',
    display: 'flex',
    gap: theme.spacing(2),
    [theme.breakpoints.up(768)]: {
      gap: theme.spacing(3),
    },
  },
  userMenuContainer: {
    marginLeft: 'auto',
  },
}));

const Header = (): JSX.Element => {
  const classes = useStyles();
  const headerRef = useRef<HTMLElement>(null);

  return (
    <header className={classes.header} ref={headerRef}>
      <div className={classes.leftContainer}>
        <div className={classes.item}>
          <PollerMenu />
        </div>
        <div className={classes.item}>
          <ServiceStatusCounter />
        </div>
        <div className={classes.item}>
          <HostStatusCounter />
        </div>
        <div className={classes.item}>
          <FederatedComponent path="/bam/header/topCounter" />
        </div>
      </div>
      <div className={classes.userMenuContainer}>
        <UserMenu headerRef={headerRef} />
      </div>
    </header>
  );
};

export default Header;
