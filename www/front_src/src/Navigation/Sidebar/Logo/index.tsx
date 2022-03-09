import React from 'react';

import { useAtomValue } from 'jotai/utils';
import { equals } from 'ramda';

import { ThemeMode, userAtom } from '@centreon/ui-context';

import logoDark from './Centreon_Logo_Noir_RVB.svg';
import logoLight from './Centreon_Logo_Blanc.svg';

interface Props {
  onClick?: () => void;
}

const Logo = ({ onClick }: Props): JSX.Element => {
  const { themeMode } = useAtomValue(userAtom);
  const logo = equals(themeMode, ThemeMode.light) ? logoDark : logoLight;

  return (
    <div aria-hidden onClick={onClick}>
      <img alt="" height="40" src={logo} width={135} />
    </div>
  );
};

export default Logo;
