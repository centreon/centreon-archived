/* eslint-disable react/jsx-wrap-multilines */
/* eslint-disable camelcase */
/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable react/no-unused-prop-types */
/* eslint-disable radix */
/* eslint-disable react/prop-types */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable no-return-assign */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable react/jsx-filename-extension */

import React, { Component } from 'react';
import classnames from 'classnames';
import * as yup from 'yup';
import PropTypes from 'prop-types';
import numeral from 'numeral';
import { Link } from 'react-router-dom';
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

const numberFormat = yup
  .number()
  .required()
  .integer();

const statusSchema = yup.object().shape({
  critical: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat,
  }),
  warning: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat,
  }),
  unknown: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat,
  }),
  ok: numberFormat,
  pending: numberFormat,
  total: numberFormat,
  refreshTime: numberFormat,
});

class ServiceStatusMenu extends Component {
  servicesStatusService = axios(
    'internal.php?object=centreon_topcounter&action=servicesStatus',
  );

  refreshInterval = null;

  state = {
    toggled: false,
    data: null,
    intervalApplied: false,
  };

  componentDidMount() {
    window.addEventListener('mousedown', this.handleClick, false);
  }

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
    clearInterval(this.refreshInterval);
  }

  // fetch api to get service data
  getData = () => {
    this.servicesStatusService
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

  // display/hide detailed service data
  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled,
    });
  };

  // hide service detailed data if click outside
  handleClick = (e) => {
    if (!this.service || this.service.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false,
    });
  };

  render() {
    const { data, toggled } = this.state;

    // do not display service information until having data
    if (!data) {
      return null;
    }

    return (
      <div
        className={`${styles.wrapper} wrap-right-services`}
        ref={(service) => (this.service = service)}
      >
        <SubmenuHeader submenuType="top" active={toggled}>
          <IconHeader
            iconType="services"
            iconName="services"
            onClick={this.toggle}
          />
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to="/main.php?p=20201&o=svc_unhandled&statusFilter=critical&search="
          >
            <IconNumber
              iconType={`${
                data.critical.unhandled > 0 ? 'colored' : 'bordered'
              }`}
              iconColor="red"
              iconNumber={
                <span id="count-svc-critical">
                  {numeral(data.critical.unhandled).format('0a')}
                </span>
              }
            />
          </Link>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to="/main.php?p=20201&o=svc_unhandled&statusFilter=warning&search="
          >
            <IconNumber
              iconType={`${
                data.warning.unhandled > 0 ? 'colored' : 'bordered'
              }`}
              iconColor="orange"
              iconNumber={
                <span id="count-svc-warning">
                  {numeral(data.warning.unhandled).format('0a')}
                </span>
              }
            />
          </Link>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to="/main.php?p=20201&o=svc_unhandled&statusFilter=unknown&search="
          >
            <IconNumber
              iconType={`${
                data.unknown.unhandled > 0 ? 'colored' : 'bordered'
              }`}
              iconColor="gray-light"
              iconNumber={
                <span id="count-svc-unknown">
                  {numeral(data.unknown.unhandled).format('0a')}
                </span>
              }
            />
          </Link>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to="/main.php?p=20201&o=svc&statusFilter=ok&search="
          >
            <IconNumber
              iconType={`${data.ok > 0 ? 'colored' : 'bordered'}`}
              iconColor="green"
              iconNumber={
                <span id="count-svc-ok">{numeral(data.ok).format('0a')}</span>
              }
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
                to="/main.php?p=20201&o=svc&statusFilter=&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  submenuTitle={I18n.t('All')}
                  submenuCount={numeral(data.total).format()}
                />
              </Link>
              <Link
                to="/main.php?p=20201&o=svc_unhandled&statusFilter=critical&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="red"
                  submenuTitle={I18n.t('Critical')}
                  submenuCount={`${numeral(
                    data.critical.unhandled,
                  ).format()}/${numeral(data.critical.total).format()}`}
                />
              </Link>
              <Link
                to="/main.php?p=20201&o=svc_unhandled&statusFilter=warning&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="orange"
                  submenuTitle={I18n.t('Warning')}
                  submenuCount={`${numeral(
                    data.warning.unhandled,
                  ).format()}/${numeral(data.warning.total).format()}`}
                />
              </Link>
              <Link
                to="/main.php?p=20201&o=svc_unhandled&statusFilter=unknown&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="gray"
                  submenuTitle={I18n.t('Unknown')}
                  submenuCount={`${numeral(
                    data.unknown.unhandled,
                  ).format()}/${numeral(data.unknown.total).format()}`}
                />
              </Link>
              <Link
                to="/main.php?p=20201&o=svc&statusFilter=ok&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="green"
                  submenuTitle={I18n.t('Ok')}
                  submenuCount={numeral(data.ok).format()}
                />
              </Link>
              <Link
                to="/main.php?p=20201&o=svc&statusFilter=pending&search="
                className={styles.link}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="blue"
                  submenuTitle={I18n.t('Pending')}
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

export default connect(mapStateToProps, mapDispatchToProps)(ServiceStatusMenu);

ServiceStatusMenu.propTypes = {
  refreshTime: PropTypes.oneOfType([PropTypes.number, PropTypes.bool])
    .isRequired,
};
