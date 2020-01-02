/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable no-shadow */

import React, { Component } from 'react';
import { connect } from 'react-redux';
import classnames from 'classnames';
import styles from './header.scss';

import Hook from '../hook/index.tsx';

import { setRefreshIntervals } from '../../redux/actions/refreshActions.ts';

import PollerMenu from '../pollerMenu/index.tsx';
import UserMenu from '../userMenu/index.tsx';
import HostMenu from '../hostMenu/index.tsx';
import ServiceStatusMenu from '../serviceStatusMenu/index.tsx';

import axios from '../../axios/index.ts';

interface Props {
  setRefreshIntervals: Function;
}

class TopHeader extends Component<Props> {
  private refreshIntervalsApi = axios(
    'internal.php?object=centreon_topcounter&action=refreshIntervals',
  );

  private getRefreshIntervals = () => {
    const { setRefreshIntervals } = this.props;
    this.refreshIntervalsApi
      .get()
      .then(({ data }) => {
        setRefreshIntervals(data);
      })
      .catch((err) => {
        console.log(err);
      });
  };

  public componentDidMount = () => {
    this.getRefreshIntervals();
  };

  public render() {
    return (
      <header className={styles.header}>
        <div className={styles['header-icons']}>
          <div className={classnames(styles.wrap, styles['wrap-left'])}>
            <PollerMenu />
          </div>
          <div className={classnames(styles.wrap, styles['wrap-right'])}>
            <Hook path="/header/topCounter" />
            <HostMenu />
            <ServiceStatusMenu />
            <UserMenu />
          </div>
        </div>
      </header>
    );
  }
}

const mapStateToProps = () => ({});

const mapDispatchToProps = {
  setRefreshIntervals,
};

export default connect(mapStateToProps, mapDispatchToProps)(TopHeader);
