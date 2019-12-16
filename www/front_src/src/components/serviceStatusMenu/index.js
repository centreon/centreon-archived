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
import { Translate } from 'react-redux-i18n';
import { connect } from 'react-redux';
import axios from '../../axios';

import styles from '../header/header.scss';

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

  componentWillReceiveProps = (nextProps) => {
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
        className={classnames(styles['wrap-right-services'], {
          [styles['submenu-active']]: toggled,
        })}
      >
        <Link
          to="/main.php?p=20201&o=svc_unhandled&statusFilter=critical&search="
          className={classnames(
            styles['wrap-middle-icon'],
            styles.round,
            styles['round-small'],
            {
              [styles[
                data.critical.unhandled > 0 ? 'red' : 'red-bordered'
              ]]: true,
            },
          )}
        >
          <span className={styles.number}>
            <span id="count-svc-critical">
              {numeral(data.critical.unhandled).format('0a')}
            </span>
          </span>
        </Link>
        <Link
          to="/main.php?p=20201&o=svc_unhandled&statusFilter=warning&search="
          className={classnames(
            styles['wrap-middle-icon'],
            styles.round,
            styles['round-small'],
            {
              [styles[
                data.warning.unhandled > 0 ? 'orange' : 'orange-bordered'
              ]]: true,
            },
          )}
        >
          <span className={styles.number}>
            <span id="count-svc-warning">
              {numeral(data.warning.unhandled).format('0a')}
            </span>
          </span>
        </Link>
        <Link
          to="/main.php?p=20201&o=svc_unhandled&statusFilter=unknown&search="
          className={classnames(
            styles['wrap-middle-icon'],
            styles.round,
            styles['round-small'],
            {
              [styles[
                data.unknown.unhandled > 0
                  ? 'gray-light'
                  : 'gray-light-bordered'
              ]]: true,
            },
          )}
        >
          <span className={styles.number}>
            <span id="count-svc-unknown">
              {numeral(data.unknown.unhandled).format('0a')}
            </span>
          </span>
        </Link>
        <Link
          to="/main.php?p=20201&o=svc&statusFilter=ok&search="
          className={classnames(
            styles['wrap-middle-icon'],
            styles.round,
            styles['round-small'],
            { [styles[data.ok > 0 ? 'green' : 'green-bordered']]: true },
          )}
        >
          <span className={styles.number}>
            <span id="count-svc-ok">{numeral(data.ok).format('0a')}</span>
          </span>
        </Link>
        <div ref={(service) => (this.service = service)}>
          <span
            className={styles['wrap-right-icon']}
            onClick={this.toggle.bind(this)}
          >
            <span
              className={classnames(styles.iconmoon, styles['icon-services'])}
            >
              {data.pending > 0 ? (
                <span className={styles['custom-icon']} />
              ) : null}
            </span>
            <span className={styles['wrap-right-icon__name']}>Services</span>
          </span>
          <span
            ref={this.setWrapperRef}
            className={styles['toggle-submenu-arrow']}
            onClick={this.toggle.bind(this)}
          >
            {this.props.children}
          </span>
          <div className={classnames(styles.submenu, styles.services)}>
            <div className={styles['submenu-inner']}>
              <ul
                className={classnames(
                  styles['submenu-items'],
                  styles['list-unstyled'],
                )}
              >
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20201&o=svc&statusFilter=&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <span>
                        <Translate value="All Services" />
                        {':'}
                      </span>
                      <span className={styles['submenu-count']}>
                        {numeral(data.total).format()}
                      </span>
                    </div>
                  </Link>
                </li>
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20201&o=svc_unhandled&statusFilter=critical&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <span
                        className={classnames(
                          styles['dot-colored'],
                          styles.red,
                        )}
                      >
                        <Translate value="Critical services" />
                        {':'}
                      </span>
                      <span className={styles['submenu-count']}>
                        {numeral(data.critical.unhandled).format()}
                        {'/'}
                        {numeral(data.critical.total).format()}
                      </span>
                    </div>
                  </Link>
                </li>
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20201&o=svc_unhandled&statusFilter=warning&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <span
                        className={classnames(
                          styles['dot-colored'],
                          styles.orange,
                        )}
                      >
                        <Translate value="Warning services" />
                        {':'}
                      </span>
                      <span className={styles['submenu-count']}>
                        {numeral(data.warning.unhandled).format()}
                        {'/'}
                        {numeral(data.warning.total).format()}
                      </span>
                    </div>
                  </Link>
                </li>
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20201&o=svc_unhandled&statusFilter=unknown&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <span
                        className={classnames(
                          styles['dot-colored'],
                          styles['gray-light'],
                        )}
                      >
                        <Translate value="Unknown services" />
                        {':'}
                      </span>
                      <span className={styles['submenu-count']}>
                        {numeral(data.unknown.unhandled).format()}
                        {'/'}
                        {numeral(data.unknown.total).format()}
                      </span>
                    </div>
                  </Link>
                </li>
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20201&o=svc&statusFilter=ok&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <span
                        className={classnames(
                          styles['dot-colored'],
                          styles.green,
                        )}
                      >
                        <Translate value="Ok services" />
                        {':'}
                      </span>
                      <span className={styles['submenu-count']}>
                        {numeral(data.ok).format()}
                      </span>
                    </div>
                  </Link>
                </li>
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20201&o=svc&statusFilter=pending&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <span
                        className={classnames(
                          styles['dot-colored'],
                          styles.blue,
                        )}
                      >
                        <Translate value="Pending services" />
                        {':'}
                      </span>
                      <span className={styles['submenu-count']}>
                        {numeral(data.pending).format()}
                      </span>
                    </div>
                  </Link>
                </li>
              </ul>
            </div>
          </div>
        </div>
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

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(ServiceStatusMenu);

ServiceStatusMenu.propTypes = {
  refreshTime: PropTypes.oneOfType([PropTypes.number, PropTypes.bool])
    .isRequired,
};
