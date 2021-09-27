/* eslint-disable react/prop-types */
/* eslint-disable camelcase */
/* eslint-disable no-console */
/* eslint-disable no-shadow */

import React from 'react';

import { connect } from 'react-redux';
import classnames from 'classnames';

import { useUserContext } from '@centreon/ui-context';

import Hook from '../Hook';
import { setRefreshIntervals } from '../../redux/actions/refreshActions';
import axios from '../../axios';

import styles from './header.scss';
import PollerMenu from './pollerMenu';
import UserMenu from './userMenu';
import HostMenu from './hostMenu';
import ServiceStatusMenu from './serviceStatusMenu';

const Header = ({ setRefreshIntervals }) => {
  const { use_deprecated_pages } = useUserContext();

  const refreshIntervalsApi = axios(
    'internal.php?object=centreon_topcounter&action=refreshIntervals',
  );

  const getRefreshIntervals = () => {
    refreshIntervalsApi
      .get()
      .then(({ data }) => {
        setRefreshIntervals(data);
      })
      .catch((err) => {
        console.log(err);
      });
  };

  React.useEffect(() => {
    getRefreshIntervals();
  }, []);

  return (
    <header className={styles.header}>
      <div className={styles['header-icons']}>
        <div className={classnames(styles.wrap, styles['wrap-left'])}>
          <PollerMenu />
        </div>
        <div className={classnames(styles.wrap, styles['wrap-right'])}>
          <Hook path="/header/topCounter" />
          <HostMenu useDeprecatedPages={use_deprecated_pages} />
          <ServiceStatusMenu useDeprecatedPages={use_deprecated_pages} />
          <UserMenu />
        </div>
      </div>
    </header>
  );
};

const mapStateToProps = () => ({});

const mapDispatchToProps = {
  setRefreshIntervals,
};

export default connect(mapStateToProps, mapDispatchToProps)(Header);
