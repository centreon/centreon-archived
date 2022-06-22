import { useState, useTransition, SetStateAction } from 'react';

import clsx from 'clsx';
import { equals } from 'ramda';
import { useLocation } from 'react-router-dom';

import { ListItemText, Switch } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { patchData, useRequest } from '@centreon/ui';
import { ThemeMode, User } from '@centreon/ui-context';

import { enhancedComponent } from './enhancedComponent';

const useStyles = makeStyles((theme) => ({
  container: {
    '& .MuiSwitch-thumb': {
      backgroundColor: 'white',
    },
    '& .MuiSwitch-track': {
      backgroundColor: '#aab4be',
      opacity: 1,
    },
    alignItems: 'center',
    display: 'flex',
  },
  containerMode: {
    display: 'flex',
    justifyContent: 'space-around',
  },
  containerSwitch: {
    '&.Mui-checked': {
      '&:hover': {
        backgroundColor: 'unset',
      },
    },
    '&:hover': {
      backgroundColor: 'unset',
    },
  },
  disabledMode: {
    color: theme.palette.common.white,
    opacity: 0.5,
  },
  mode: {
    paddingLeft: theme.spacing(1),
  },
}));

interface Props {
  setUser: (update: SetStateAction<User>) => void;
  user: User;
}

const SwitchThemeMode = ({ user, setUser }: Props): JSX.Element => {
  const classes = useStyles();
  const { pathname } = useLocation();
  const isDarkMode = equals(user.themeMode, ThemeMode.dark);

  const [isDark, setIsDark] = useState(isDarkMode);

  const { sendRequest } = useRequest({
    request: patchData,
  });
  const [isPending, startTransition] = useTransition();

  const switchEndPoint = './api/latest/configuration/users/current/parameters';

  const switchThemeMode = (): void => {
    const themeMode = isDarkMode ? ThemeMode.light : ThemeMode.dark;
    const isCurrentPageLegacy = pathname.includes('php');
    setIsDark(!isDark);
    startTransition(() => {
      setUser({
        ...user,
        themeMode,
      });
    });
    sendRequest({
      data: { theme: themeMode },
      endpoint: switchEndPoint,
    }).then(() => {
      if (isCurrentPageLegacy) {
        window.location.reload();
      }
    });
  };

  return (
    <div className={classes.container}>
      <Switch
        checked={isDark}
        className={classes.containerSwitch}
        disabled={isPending}
        size="small"
        onChange={switchThemeMode}
      />
      <div className={classes.containerMode}>
        <ListItemText
          className={clsx(classes.mode, { [classes.disabledMode]: isDark })}
        >
          Light
        </ListItemText>

        <ListItemText
          className={clsx(classes.mode, {
            [classes.disabledMode]: !isDark,
          })}
        >
          Dark
        </ListItemText>
      </div>
    </div>
  );
};

export default enhancedComponent(SwitchThemeMode);
