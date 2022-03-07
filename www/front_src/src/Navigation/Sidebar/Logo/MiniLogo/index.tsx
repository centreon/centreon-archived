import React from 'react';

import { useAtomValue } from 'jotai/utils';
import { equals } from 'ramda';

import { ThemeMode, userAtom } from '@centreon/ui-context';

import miniLogoDark from './Centreon_Logo_Noir_RVB_C.svg';
import miniLogoLight from './Centreon_Logo_Blanc_C.svg';

interface Props {
  onClick?: () => void;
}

const MiniLogo = ({ onClick }: Props): JSX.Element => {
  const { themeMode } = useAtomValue(userAtom);
  const miniLogo = equals(themeMode, ThemeMode.light)
    ? miniLogoDark
    : miniLogoLight;

  return (
    <div aria-hidden onClick={onClick}>
      <img alt="" height="52" src={miniLogo} width={30} />
    </div>
  );
};

export default MiniLogo;
