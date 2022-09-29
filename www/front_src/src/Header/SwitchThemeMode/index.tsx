import { useState } from 'react';

import clsx from 'clsx';
import { useLocation } from 'react-router-dom';

import { ListItemText, Switch } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { patchData, useRequest } from '@centreon/ui';

import useSwitchThemeMode from './useSwitchThemeMode';

const useStyles = makeStyles((theme) => ({
  container: {
    '& .MuiSwitch-thumb': {
      backgroundColor: 'white',
    },
    '& .MuiSwitch-track, & .Mui-checked + .MuiSwitch-track': {
      backgroundColor: theme.palette.text.primary,
      opacity: 0.5,
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
    opacity: 0.5,
  },
  mode: {
    paddingLeft: theme.spacing(1),
  },
}));

const SwitchThemeMode = (): JSX.Element => {
  const classes = useStyles();
  const { pathname } = useLocation();
  const [isPending, isDarkMode, themeMode, updateUser] = useSwitchThemeMode();

  const [isDark, setIsDark] = useState(isDarkMode);

  const { sendRequest } = useRequest({
    request: patchData,
  });

  const switchEndPoint = './api/latest/configuration/users/current/parameters';

  const switchThemeMode = (): void => {
    const isCurrentPageLegacy = pathname.includes('php');
    setIsDark(!isDark);
    updateUser();
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
        data-cy="themeSwitch"
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

export default SwitchThemeMode;
