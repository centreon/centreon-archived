/* eslint-disable no-undef */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable no-shadow */

import * as React from 'react';

import { useMemoComponent } from '@centreon/ui';

import Sidebar from './Sidebar';
import useNavigation from './useNavigation';

const Navigation = (): JSX.Element => {
  const { reactRoutes, menu } = useNavigation();

  return useMemoComponent({
    Component: <Sidebar navigationData={menu} reactRoutes={reactRoutes} />,
    memoProps: [menu, reactRoutes],
  });
};

export default Navigation;
