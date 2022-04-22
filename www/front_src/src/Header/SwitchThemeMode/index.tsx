import * as React from 'react';

import { equals } from 'ramda';
import { useAtom } from 'jotai';
import { useLocation } from 'react-router-dom';

import { styled } from '@mui/material/styles';
import Switch from '@mui/material/Switch';
import makeStyles from '@mui/styles/makeStyles';

import { userAtom, ThemeMode } from '@centreon/ui-context';
import { patchData, useRequest } from '@centreon/ui';

import svgSun from './images/sun.svg';
import svgMoon from './images/moon.svg';

interface StyleProps {
  darkModeSvg?: string;
  lightModeSvg?: string;
}

const ThemeModeSwitch = styled(Switch, {
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
        backgroundImage: `url(${darkModeSvg})`,
      },
      color: 'transparent',
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
      backgroundImage: `url(${lightModeSvg})`,
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
  const props = {
    darkModeSvg: svgMoon,
    lightModeSvg: svgSun,
  };
  const classes = useStyles();
  const { pathname } = useLocation();

  const { sendRequest } = useRequest({
    request: patchData,
  });
  const [user, setUser] = useAtom(userAtom);

  const isDarkMode = equals(user.themeMode, ThemeMode.dark);
  const switchEndPoint = './api/latest/configuration/users/current/parameters';

  const switchThemeMode = (): void => {
    const themeMode = isDarkMode ? ThemeMode.light : ThemeMode.dark;
    const isCurrentPageLegacy = pathname.includes('php');
    setUser({
      ...user,
      themeMode,
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
        checked={isDarkMode}
        {...props}
        onChange={switchThemeMode}
      />
    </div>
  );
};

export default SwitchThemeMode;
