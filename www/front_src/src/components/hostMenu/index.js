/* eslint-disable react/jsx-wrap-multilines */
/* eslint-disable camelcase */
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
import { I18n } from 'react-redux-i18n';

import {
  IconHeader,
  IconNumber,
  IconToggleSubmenu,
  SubmenuHeader,
  SubmenuItem,
  SubmenuItems,
} from '@centreon/ui';

import styles from '../header/header.scss';
import axios from '../../axios';

const numberFormat = yup.number().required().integer();

const statusSchema = yup.object().shape({
  down: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat,
  }),
  ok: numberFormat,
  pending: numberFormat,
  refreshTime: numberFormat,
  total: numberFormat,
  unreachable: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat,
  }),
});

class HostMenu extends Component {
  hostsService = axios(
    'internal.php?object=centreon_topcounter&action=hosts_status',
  );

  refreshInterval = null;

  state = {
    data: null,
    intervalApplied: false,
    toggled: false,
  };

  componentDidMount() {
    window.addEventListener('mousedown', this.handleClick, false);
  }

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
    clearInterval(this.refreshInterval);
  }

  // fetch api to get host data
  getData = () => {
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

  UNSAFE_componentWillReceiveProps = (nextProps) => {
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
  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled,
    });
  };

  // hide host detailed data if click outside
  handleClick = (e) => {
    if (!this.host || this.host.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false,
    });
  };

  render() {
    const { data, toggled } = this.state;

    // do not display host information until having data
    if (!data) {
      return null;
    }

    return (
      <div
        className={`${styles.wrapper} wrap-right-hosts`}
        ref={(host) => (this.host = host)}
      >
        <SubmenuHeader active={toggled} submenuType="top">
          <IconHeader
            iconName={I18n.t('Hosts')}
            iconType="hosts"
            onClick={this.toggle}
          >
            {data.pending > 0 && <span className={styles['custom-icon']} />}
          </IconHeader>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to="/main.php?p=20202&o=h_down&search="
          >
            <IconNumber
              iconColor="red"
              iconNumber={
                <span id="count-host-down">
                  {numeral(data.down.unhandled).format('0a')}
                </span>
              }
              iconType={`${data.down.unhandled > 0 ? 'colored' : 'bordered'}`}
            />
          </Link>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to="/main.php?p=20202&o=h_unreachable&search="
          >
            <IconNumber
              iconColor="gray-dark"
              iconNumber={
                <span id="count-host-unreachable">
                  {numeral(data.unreachable.unhandled).format('0a')}
                </span>
              }
              iconType={`${
                data.unreachable.unhandled > 0 ? 'colored' : 'bordered'
              }`}
            />
          </Link>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to="/main.php?p=20202&o=h_up&search="
          >
            <IconNumber
              iconColor="green"
              iconNumber={
                <span id="count-host-up">{numeral(data.ok).format('0a')}</span>
              }
              iconType={`${data.ok > 0 ? 'colored' : 'bordered'}`}
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
                className={styles.link}
                to="/main.php?p=20202&o=h&search="
                onClick={this.toggle}
              >
                <SubmenuItem
                  submenuCount={numeral(data.total).format()}
                  submenuTitle={I18n.t('All')}
                />
              </Link>
              <Link
                className={styles.link}
                to="/main.php?p=20202&o=h_down&search="
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="red"
                  submenuCount={`${numeral(data.down.unhandled).format(
                    '0a',
                  )}/${numeral(data.down.total).format('0a')}`}
                  submenuTitle={I18n.t('Down')}
                />
              </Link>
              <Link
                className={styles.link}
                to="/main.php?p=20202&o=h_unreachable&search="
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="gray"
                  submenuCount={`${numeral(data.unreachable.unhandled).format(
                    '0a',
                  )}/${numeral(data.unreachable.total).format('0a')}`}
                  submenuTitle={I18n.t('Unreachable')}
                />
              </Link>
              <Link
                className={styles.link}
                to="/main.php?p=20202&o=h_up&search="
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="green"
                  submenuCount={numeral(data.ok).format()}
                  submenuTitle={I18n.t('Up')}
                />
              </Link>
              <Link
                className={styles.link}
                to="/main.php?p=20202&o=h_pending&search="
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="blue"
                  submenuCount={numeral(data.pending).format()}
                  submenuTitle={I18n.t('Pending')}
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
