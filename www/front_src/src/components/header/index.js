/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable no-shadow */

import React, { Component } from 'react';
import { connect } from 'react-redux';
import classnames from 'classnames';
import styles from './header.scss';

import Hook from '../hook';

import { setRefreshIntervals } from '../../redux/actions/refreshActions';

import PollerMenu from '../pollerMenu';
import UserMenu from '../userMenu';
import HostMenu from '../hostMenu';
import ServiceStatusMenu from '../serviceStatusMenu';

import axios from '../../axios';

class TopHeader extends Component {
  refreshIntervalsApi = axios(
    'internal.php?object=centreon_topcounter&action=refreshIntervals',
  );

  getRefreshIntervals = () => {
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

  componentDidMount = () => {
    this.getRefreshIntervals();
  };

  render() {
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

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(TopHeader);
