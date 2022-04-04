import * as React from 'react';

import { useAtomValue } from 'jotai/utils';
import { equals } from 'ramda';
import { useTranslation } from 'react-i18next';
import clsx from 'clsx';

import { makeStyles } from '@mui/styles';

import { ThemeMode, userAtom } from '@centreon/ui-context';

import logoCentreon from '../assets/centreon.png';
import logoDark from '../assets/Centreon_Logo_Blanc.svg';

import { labelCentreonLogo } from './translatedLabels';

const useStyles = makeStyles({
  centreonLogo: {
    height: 'auto',
    width: 'auto',
  },
  centreonLogoDark: {
    height: 57,
    width: 250,
  },
});

const Logo = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { themeMode } = useAtomValue(userAtom);
  const logo = equals(themeMode, ThemeMode.light) ? logoCentreon : logoDark;
  const isDarkMode = equals(themeMode, ThemeMode.dark);

  return (
    <img
      alt={t(labelCentreonLogo)}
      aria-label={t(labelCentreonLogo)}
      className={clsx(classes.centreonLogo, {
        [classes.centreonLogoDark]: isDarkMode,
      })}
      src={logo}
    />
  );
};

export default Logo;
