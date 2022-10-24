import { useMemoComponent } from '@centreon/ui';

import Sidebar from './Sidebar';
import useNavigation from './useNavigation';

const Navigation = (): JSX.Element => {
  const { menu } = useNavigation();

  return useMemoComponent({
    Component: <Sidebar navigationData={menu} />,
    memoProps: [menu]
  });
};

export default Navigation;
