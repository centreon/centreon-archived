import React from 'react';

import { useAtomValue } from 'jotai/utils';
import { equals } from 'ramda';

import { makeStyles } from '@mui/styles';

import { ThemeMode, userAtom } from '@centreon/ui-context';

import logoDark from './Centreon_Logo_Noir_RVB.svg';
import logoLight from './Centreon_Logo_Blanc.svg';
import logoMinDark from './Centreon_Logo_Noir_RVB_C.svg';
import logoMinLight from './Centreon_Logo_Blanc_C.svg';

interface Props {
  isDrawerOpen?: boolean;
  onClick?: () => void;
}
const useStyles = makeStyles(() => ({
  logo: {
    '&:hover': {
      cursor: 'pointer',
    },
  },
}));

const Logo = ({ onClick, isDrawerOpen }: Props): JSX.Element => {
  const classes = useStyles();
  const { themeMode } = useAtomValue(userAtom);
  const logo = equals(themeMode, ThemeMode.light) ? logoDark : logoLight;
  const logoMin = equals(themeMode, ThemeMode.light)
    ? logoMinDark
    : logoMinLight;

  return (
    <div aria-hidden className={classes.logo} onClick={onClick}>
      <span>
        <img
          alt=""
          height="52"
          src={isDrawerOpen ? logo : logoMin}
          width={isDrawerOpen ? '180' : '30'}
        />
      </span>
    </div>
  );
};

export default Logo;
