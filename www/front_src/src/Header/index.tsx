import * as React from 'react';

import clsx from 'clsx';

import Hook from '../components/Hook';

import styles from './header.scss';
import PollerMenu from './PollerMenu';
import UserMenu from './userMenu';
import ServiceStatusCounter from './RessourceStatusCounter/Service';
import HostStatusCounter from './RessourceStatusCounter/Host';

const HookComponent = Hook as unknown as (props) => JSX.Element;

const Header = (): JSX.Element => {
  return (
    <header className={styles.header}>
      <div className={styles['header-icons']}>
        <div className={clsx(styles.wrap, styles['wrap-left'])}>
          <PollerMenu />
        </div>
        <div className={clsx(styles.wrap, styles['wrap-right'])}>
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
