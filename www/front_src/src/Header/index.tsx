import React from 'react';

import classnames from 'classnames';
import { useAtomValue } from 'jotai/utils';

import { refreshIntervalAtom } from '@centreon/ui-context';

import Hook from '../components/Hook';

import styles from './header.scss';
import PollerMenu, { pollerConfigurationNumberPage } from './PollerMenu';
import UserMenu from './userMenu';
import ServiceStatusCounter from './RessourceStatusCounter/Service';
import HostStatusCounter from './RessourceStatusCounter/Host';

const HookComponent = Hook as unknown as (props) => JSX.Element;

const Header = (): JSX.Element => {
  const refreshInterval = useAtomValue(refreshIntervalAtom);

  const pollerListIssues =
    'internal.php?object=centreon_topcounter&action=pollersListIssues';

  return (
    <header className={styles.header}>
      <div className={styles['header-icons']}>
        <div className={classnames(styles.wrap, styles['wrap-left'])}>
          <PollerMenu
            allowedPages={[pollerConfigurationNumberPage]}
            endpoint={pollerListIssues}
            loaderWidth={27}
            refreshInterval={refreshInterval}
          />
        </div>
        <div className={classnames(styles.wrap, styles['wrap-right'])}>
          <HookComponent path="/header/topCounter" />
          <HostStatusCounter />
          <ServiceStatusCounter />
          <UserMenu />
        </div>
      </div>
    </header>
  );
};

export default Header;
