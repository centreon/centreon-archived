/* eslint-disable no-return-assign */
/* eslint-disable react/no-unused-prop-types */
/* eslint-disable radix */
/* eslint-disable react/prop-types */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable import/no-extraneous-dependencies */

import React, { Component } from 'react';
import classnames from 'classnames';
import * as yup from 'yup';
import numeral from 'numeral';
import { Link } from 'react-router-dom';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';

import IconHeader from '@centreon/ui/Icon/IconHeader';
import IconNumber from '@centreon/ui/Icon/IconNumber';
import IconToggleSubmenu from '@centreon/ui/Icon/IconToggleSubmenu';
import SubmenuHeader from '@centreon/ui/Submenu/SubmenuHeader';
import SubmenuItem from '@centreon/ui/Submenu/SubmenuHeader/SubmenuItem';
import SubmenuItems from '@centreon/ui/Submenu/SubmenuHeader/SubmenuItems';

import styles from '../header/header.scss';
import axios from '../../axios';

const numberFormat = yup
  .number()
  .required()
  .integer();

const statusSchema = yup.object().shape({
  down: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat,
  }),
  unreachable: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat,
  }),
  ok: numberFormat,
  pending: numberFormat,
  total: numberFormat,
  refreshTime: numberFormat,
});

export interface StatusDetails {
  total: number;
  unhandled: number;
}

interface Data {
  down: StatusDetails;
  unreachable: StatusDetails;
  ok: number;
  pending: number;
  total: number;
  refreshTime: number;
}

export interface TopCounterState {
  toggled: boolean;
  data: Data;
  intervalApplied: boolean;
}

class HostMenu extends Component<TopCounterState> {
  private hostsService = axios(
    'internal.php?object=centreon_topcounter&action=hosts_status'
  );

  private refreshInterval = null;

  public state = {
    toggled: false,
    data: null,
    intervalApplied: false,
  };

  public componentDidMount() {
    window.addEventListener('mousedown', this.handleClick, false);
  }

  public componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
    clearInterval(this.refreshInterval);
  }

  // fetch api to get host data
  private getData = () => {
    this.hostsService
      .get()
      .then(({ data }) => {
        statusSchema.validate(data).then(() => {
          this.setState({ data });
        });
      })
      .catch((error) => {
        if (error.response && error.response.status === 401) {
          this.setState({
            data: null,
          });
        }
      });
  };

  public componentWillReceiveProps = (nextProps) => {
    const { refreshTime } = nextProps;
    const { intervalApplied } = this.state;
    if (refreshTime && !intervalApplied) {
      this.getData();
      this.refreshInterval = setInterval(() => {
        this.getData();
      }, refreshTime);
      this.setState({
        intervalApplied: true,
      });
    }
  };

  // display/hide detailed host data
  private toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled,
    });
  };

  // hide host detailed data if click outside
  private handleClick = (e) => {
    if (!this.host || this.host.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false,
    });
  };

  public render() {
    const { data, toggled } = this.state;

    // do not display host information until having data
    if (!data) {
      return null;
    }

    return (
      <div className={styles.wrapper} ref={(host) => (this.host = host)}>
        <SubmenuHeader submenuType="top" active={toggled}>
          <IconHeader iconType="hosts" iconName="Hosts" onClick={this.toggle} />
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to="/main.php?p=20202&o=h_down&search="
          >
            <IconNumber
              iconType={`${data.down.unhandled > 0 ? 'colored' : 'bordered'}`}
              iconColor="red"
              iconNumber={`${numeral(data.down.unhandled).format('0a')}`}
            />
          </Link>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to="/main.php?p=20202&o=h_unreachable&search="
          >
            <IconNumber
              iconType={`${
                data.unreachable.unhandled > 0 ? 'colored' : 'bordered'
              }`}
              iconColor="gray-dark"
              iconNumber={numeral(data.unreachable.unhandled).format('0a')}
            />
          </Link>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to="/main.php?p=20202&o=h_up&search="
          >
            <IconNumber
              iconType={`${data.ok > 0 ? 'colored' : 'bordered'}`}
              iconColor="green"
              iconNumber={numeral(data.ok).format('0a')}
            />
          </Link>
          <IconToggleSubmenu
            iconType="arrow"
            ref={this.setWrapperRef}
            rotate={toggled}
            onClick={this.toggle}
          />
          <div
            className={classnames(styles['submenu-toggle'], {
              [styles['submenu-toggle-active']]: toggled,
            })}
          >
            <SubmenuItems>
              <Link
                to="/main.php?p=20202&o=h&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  submenuTitle="All"
                  submenuCount={numeral(data.total).format()}
                />
              </Link>
              <Link
                to="/main.php?p=20202&o=h_down&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="red"
                  submenuTitle="Critical"
                  submenuCount={`${numeral(data.down.unhandled).format(
                    '0a'
                  )}/${numeral(data.down.total).format('0a')}`}
                />
              </Link>
              <Link
                to="/main.php?p=20202&o=h_unreachable&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="gray"
                  submenuTitle="Unreachable"
                  submenuCount={`${numeral(data.unreachable.unhandled).format(
                    '0a'
                  )}/${numeral(data.unreachable.total).format('0a')}`}
                />
              </Link>
              <Link
                to="/main.php?p=20202&o=h_up&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="green"
                  submenuTitle="Up"
                  submenuCount={numeral(data.ok).format()}
                />
              </Link>
              <Link
                to="/main.php?p=20202&o=h_pending&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="blue"
                  submenuTitle="Pending"
                  submenuCount={numeral(data.pending).format()}
                />
              </Link>
            </SubmenuItems>
          </div>
        </SubmenuHeader>
      </div>
    );
  }
}

const mapStateToProps = ({ intervals }) => ({
  refreshTime: intervals
    ? parseInt(intervals.AjaxTimeReloadMonitoring) * 1000
    : false,
});

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(HostMenu);

HostMenu.propTypes = {
  refreshTime: PropTypes.oneOfType([PropTypes.number, PropTypes.bool])
    .isRequired,
};
