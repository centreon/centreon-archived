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

export const headerHeight = 6;

export const paddingBottomHeader = 0.5;

const useStyles = makeStyles((theme) => ({
  container: {
    alignItems: 'flex-end',
    display: 'flex',
    margin: theme.spacing(0, 4, 0, 3),
    width: '100%',
  },
  header: {
    backgroundColor: isDarkMode(theme)
      ? theme.palette.background.default
      : theme.palette.primary.main,
    display: 'flex',
    height: theme.spacing(headerHeight),
    paddingBottom: theme.spacing(paddingBottomHeader),
    width: '100%',
  },
  item: {
    flex: 1,
    maxWidth: theme.spacing(15),
  },
  leftContainer: {
    alignItems: 'flex-end',
    display: 'flex',
    flex: 1,
    gap: theme.spacing(3),
    [theme.breakpoints.down(768)]: {
      gap: theme.spacing(2),
    },
  },
  userMenu: {
    display: 'flex',
    justifyContent: 'flex-end',
  },
  userMenuContainer: {
    alignItems: 'center',
    display: 'flex',
    flex: 0.5,
    justifyContent: 'flex-end',
  },
}));

const Header = (): JSX.Element => {
  const classes = useStyles();
  const headerRef = useRef<HTMLElement>(null);

  return (
    <header className={classes.header} ref={headerRef}>
      <div className={classes.container}>
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
      </div>
    </header>
  );
};

export default Header;
