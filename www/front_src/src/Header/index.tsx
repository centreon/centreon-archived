import React from 'react';

import classnames from 'classnames';

import Hook from '../components/Hook';

import styles from './header.scss';
import PollerMenu from './pollerMenu';
import UserMenu from './userMenu';
import ServiceStatusCounter from './StatusCounter/Service';
import HostStatusCounter from './StatusCounter/Host';

const Header = (): JSX.Element => {
  return (
    <header className={styles.header}>
      <div className={styles['header-icons']}>
        <div className={classnames(styles.wrap, styles['wrap-left'])}>
          <PollerMenu />
        </div>
        <div className={classnames(styles.wrap, styles['wrap-right'])}>
          {/* <Hook path="/header/topCounter" /> */}
          <HostStatusCounter />
          <ServiceStatusCounter />
          <UserMenu />
        </div>
      </div>
    </header>
  );
};

export default Header;
