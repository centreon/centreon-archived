import React from 'react';

import classnames from 'classnames';
import { useAtomValue } from 'jotai/utils';
import { equals } from 'ramda';

import { ThemeMode, userAtom } from '@centreon/ui-context';

import logoDark from './Centreon_Logo_Noir_RVB.svg';
import logoLight from './Centreon_Logo_Blanc.svg';
import logoMinDark from './Centreon_Logo_Noir_RVB_C.svg';
import logoMinLight from './Centreon_Logo_Blanc_C.svg';

interface Props {
  customClass?: string;
  isDrawerOpen?: boolean;
  onClick?: () => void;
}

const Logo = ({ customClass, onClick, isDrawerOpen }: Props): JSX.Element => {
  const { themeMode } = useAtomValue(userAtom);
  const logo = equals(themeMode, ThemeMode.light) ? logoDark : logoLight;
  const logoMin = equals(themeMode, ThemeMode.light)
    ? logoMinDark
    : logoMinLight;

  return (
    <div aria-hidden className={classnames(customClass)} onClick={onClick}>
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
