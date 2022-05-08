import { makeStyles } from '@mui/styles';

import Hook from '../components/LoadableComponents';

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
    justifyContent: 'space-between',
  },
  header: {
    background: theme.palette.common.black,
  },
  rightContainer: {
    display: 'flex',
  },
}));

const Header = (): JSX.Element => {
  const classes = useStyles();

  return (
    <header className={classes.header}>
      <div className={classes.container}>
        <div>
          <PollerMenu />
        </div>
        <div className={classes.rightContainer}>
          <HookComponent exclude="centreon-license-manager" />
          <HostStatusCounter />
          <ServiceStatusCounter />
          <SwitchMode />
          <UserMenu />
        </div>
      </div>
    </header>
  );
};

export default Header;
