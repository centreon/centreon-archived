/* eslint-disable hooks/sort */
import { useTransition, useState } from 'react';

import { equals } from 'ramda';
import { useAtom } from 'jotai';
import { useLocation } from 'react-router-dom';
import clsx from 'clsx';

import { styled } from '@mui/material/styles';
import { Switch, ListItemText } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { userAtom, ThemeMode } from '@centreon/ui-context';
import { patchData, useRequest } from '@centreon/ui';

const ThemeModeSwitch = styled(Switch)(() => ({
  '& .MuiSwitch-switchBase': {
    '&.Mui-checked': {
      '&:hover': {
        backgroundColor: 'unset',
      },
    },
    '&:hover': {
      backgroundColor: 'unset',
    },
  },
  '& .MuiSwitch-thumb': {
    backgroundColor: 'white',
  },
  '& .MuiSwitch-track': {
    backgroundColor: '#aab4be',
    opacity: 1,
  },
}));

const useStyles = makeStyles((theme) => ({
  container: {
    alignItems: 'center',
    display: 'flex',
  },
  containerMode: {
    display: 'flex',
    justifyContent: 'space-around',
  },
  disabledMode: {
    color: '#A7A7A7',
  },
  mode: {
    paddingLeft: theme.spacing(1),
  },
}));

const SwitchThemeMode = (): JSX.Element => {
  const classes = useStyles();
  const { pathname } = useLocation();
  const { sendRequest } = useRequest({
    request: patchData,
  });
  const [user, setUser] = useAtom(userAtom);
  const [, startTransition] = useTransition();
  const isDarkMode = equals(user.themeMode, ThemeMode.dark);
  const [isDark, setIsDark] = useState(isDarkMode);
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
      <ThemeModeSwitch
        checked={isDark}
        size="small"
        onChange={switchThemeMode}
      />
      <div className={classes.containerMode}>
        <ListItemText
          className={clsx(classes.mode, { [classes.disabledMode]: isDarkMode })}
        >
          Light
        </ListItemText>

        <ListItemText
          className={clsx(classes.mode, {
            [classes.disabledMode]: !isDarkMode,
          })}
        >
          Dark
        </ListItemText>
      </div>
    </div>
  );
};

export default SwitchThemeMode;
