import * as React from 'react';

import { equals } from 'ramda';
import { useAtom } from 'jotai';
import { useLocation } from 'react-router-dom';

import { styled } from '@mui/material/styles';
import Switch from '@mui/material/Switch';
import makeStyles from '@mui/styles/makeStyles';

import { userAtom, ThemeMode } from '@centreon/ui-context';
import { patchData, useRequest } from '@centreon/ui';

interface StyleProps {
  darkModeSvg?: string;
  lightModeSvg?: string;
}

const svgMoon = `url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 20 20"><path fill="${encodeURIComponent(
  'black',
)}" d="M4.2 2.5l-.7 1.8-1.8.7 1.8.7.7 1.8.6-1.8L6.7 5l-1.9-.7-.6-1.8zm15 8.3a6.7 6.7 0 11-6.6-6.6 5.8 5.8 0 006.6 6.6z"/></svg>')`;

const svgSun = `url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" height="19" width="19" viewBox="0 0 20 20"><path fill="${encodeURIComponent(
  'black',
)}" d="M9.305 1.667V3.75h1.389V1.667h-1.39zm-4.707 1.95l-.982.982L5.09 6.072l.982-.982-1.473-1.473zm10.802 0L13.927 5.09l.982.982 1.473-1.473-.982-.982zM10 5.139a4.872 4.872 0 00-4.862 4.86A4.872 4.872 0 0010 14.862 4.872 4.872 0 0014.86 10 4.872 4.872 0 0010 5.139zm0 1.389A3.462 3.462 0 0113.471 10a3.462 3.462 0 01-3.473 3.472A3.462 3.462 0 016.527 10 3.462 3.462 0 0110 6.528zM1.665 9.305v1.39h2.083v-1.39H1.666zm14.583 0v1.39h2.084v-1.39h-2.084zM5.09 13.928L3.616 15.4l.982.982 1.473-1.473-.982-.982zm9.82 0l-.982.982 1.473 1.473.982-.982-1.473-1.473zM9.305 16.25v2.083h1.389V16.25h-1.39z"/></svg>')`;

const MaterialUISwitch = styled(Switch, {
  shouldForwardProp: (prop) =>
    !equals(prop, 'color') &&
    !equals(prop, 'lightModeSvg') &&
    !equals(prop, 'darkModeSvg'),
})<StyleProps>(({ theme, darkModeSvg, lightModeSvg }) => ({
  '& .MuiSwitch-switchBase': {
    '&.Mui-checked': {
      '& + .MuiSwitch-track': {
        backgroundColor: '#aab4be',
        opacity: 1,
      },
      '& .MuiSwitch-thumb:before': {
        backgroundImage: darkModeSvg,
      },
      transform: 'translate(15px,-50%)',
    },
    '&:hover': {
      backgroundColor: 'transparent',
    },
    color: 'black',
    margin: 0,
    position: 'absolute',
    top: '50%',
    transform: 'translate(-0.5px,-50%)',
  },
  '& .MuiSwitch-thumb': {
    '&:before': {
      backgroundImage: lightModeSvg,
      backgroundPosition: 'center',
      backgroundRepeat: 'no-repeat',
      content: "''",
      height: '100%',
      left: theme.spacing(0),
      position: 'absolute',
      top: theme.spacing(0),
      width: '100%',
    },
    backgroundColor: 'white',
    height: theme.spacing(3),
    width: theme.spacing(3),
  },
  '& .MuiSwitch-track': {
    backgroundColor: '#aab4be',
    borderRadius: theme.spacing(10 / 8),
    opacity: 1,
  },
  height: theme.spacing(32 / 8),
  padding: theme.spacing(11 / 8, 4 / 8, 11 / 8, 9 / 8),
  width: theme.spacing(50 / 8),
}));

const useStyles = makeStyles((theme) => ({
  container: {
    alignItems: 'center',
    display: 'flex',
    paddingLeft: theme.spacing(7),
  },
}));

const SwitchThemeMode = (): JSX.Element => {
  const props = { darkModeSvg: svgMoon, lightModeSvg: svgSun };
  const classes = useStyles();
  const { pathname } = useLocation();

  const { sendRequest } = useRequest({
    request: patchData,
  });
  const [user, setUser] = useAtom(userAtom);

  const isDarkMode = equals(user.themeMode, ThemeMode.dark);
  const switchEndPoint = './api/latest/configuration/users/current/parameters';

  const switchThemeMode = (): void => {
    sendRequest({
      data: { theme: isDarkMode ? ThemeMode.light : ThemeMode.dark },
      endpoint: switchEndPoint,
    }).then(() => {
      if (pathname.includes('php')) {
        window.location.reload();

        return;
      }
      setUser({
        ...user,
        themeMode: isDarkMode ? ThemeMode.light : ThemeMode.dark,
      });
    });
  };

  return (
    <div className={classes.container}>
      <MaterialUISwitch
        checked={isDarkMode}
        {...props}
        onChange={switchThemeMode}
      />
    </div>
  );
};

export default SwitchThemeMode;
