import { equals } from 'ramda';
import { useAtom } from 'jotai';
import { useLocation } from 'react-router-dom';
import clsx from 'clsx';

import { styled } from '@mui/material/styles';
import { Switch, ListItemText } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { userAtom, ThemeMode } from '@centreon/ui-context';
import { patchData, useRequest } from '@centreon/ui';

const ThemeModeSwitch = styled(Switch)(({ theme }) => ({
  '& .MuiSwitch-switchBase': {
    '&.Mui-checked': {
      '& + .MuiSwitch-track': {
        backgroundColor: '#aab4be',
        opacity: 1,
      },
      '&:hover': {
        backgroundColor: 'unset',
      },
      color: 'transparent',
      transform: 'translate(23px,-52%)',
    },

    '&:hover': {
      backgroundColor: 'unset',
    },
    color: 'unset',
    left: 0,
    margin: 0,
    position: 'absolute',
    top: '50%',
    transform: 'translate(-0.5px,-52%)',
  },
  '& .MuiSwitch-thumb': {
    '&:before': {
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
    height: theme.spacing(15 / 8),
    width: theme.spacing(15 / 8),
  },
  '& .MuiSwitch-track': {
    backgroundColor: '#aab4be',
    borderRadius: theme.spacing(10 / 8),
    opacity: 1,
  },
  height: theme.spacing(37 / 8),
  padding: theme.spacing(11 / 8, 4 / 8, 11 / 8, 9 / 8),

  width: theme.spacing(50 / 8),
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
      <ThemeModeSwitch checked={isDarkMode} onChange={switchThemeMode} />
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
