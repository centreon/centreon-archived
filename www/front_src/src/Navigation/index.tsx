import * as React from 'react';

import { useMemoComponent } from '@centreon/ui';

import Sidebar from './Sidebar';
import useNavigation from './useNavigation';

const Navigation = (): JSX.Element => {
  const { reactRoutes, menu } = useNavigation();

  return useMemoComponent({
    Component: <Sidebar navigationData={menu} />,
    memoProps: [menu, reactRoutes],
  });
};

export default Navigation;
