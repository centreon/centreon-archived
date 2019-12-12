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
import numeral from "numeral";
import { Link } from 'react-router-dom';
import PropTypes from 'prop-types';
import { Translate } from 'react-redux-i18n';
import { connect } from 'react-redux';
import axios from '../../axios';

import styles from '../header/header.scss';

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

class HostMenu extends Component {
  hostsService = axios(
    'internal.php?object=centreon_topcounter&action=hosts_status',
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
        className={classnames(styles['wrap-right-hosts'], {
          [styles['submenu-active']]: toggled,
        })}
      >
        <Link
          to="/main.php?p=20202&o=h_down&search="
          className={classnames(
            styles['wrap-middle-icon'],
            styles.round,
            styles['round-small'],
            {
              [styles[data.down.unhandled > 0 ? 'red' : 'red-bordered']]: true,
            },
          )}
        >
          <span className={styles.number}>
            <span id="count-host-down">
              {numeral(data.down.unhandled).format('0a')}
            </span>
          </span>
        </Link>
        <Link
          to="/main.php?p=20202&o=h_unreachable&search="
          className={classnames(
            styles['wrap-middle-icon'],
            styles.round,
            styles['round-small'],
            {
              [styles[
                data.unreachable.unhandled > 0
                  ? 'gray-dark'
                  : 'gray-dark-bordered'
              ]]: true,
            },
          )}
        >
          <span className={styles.number}>
            <span id="count-host-unreachable">
              {numeral(data.unreachable.unhandled).format('0a')}
            </span>
          </span>
        </Link>
        <Link
          to="/main.php?p=20202&o=h_up&search="
          className={classnames(
            styles['wrap-middle-icon'],
            styles.round,
            styles['round-small'],
            { [styles[data.ok > 0 ? 'green' : 'green-bordered']]: true },
          )}
        >
          <span className={styles.number}>
            <span id="count-host-up">{numeral(data.ok).format('0a')}</span>
          </span>
        </Link>
        <div ref={(host) => (this.host = host)}>
          <span
            className={classnames(styles['wrap-right-icon'], styles.hosts)}
            onClick={this.toggle.bind(this)}
          >
            <span className={classnames(styles.iconmoon, styles['icon-hosts'])}>
              {data.pending > 0 ? (
                <span className={styles['custom-icon']} />
              ) : null}
            </span>
            <span className={styles['wrap-right-icon__name']}>
              <Translate value="Hosts" />
            </span>
          </span>
          <span
            className={styles['toggle-submenu-arrow']}
            onClick={this.toggle.bind(this)}
          >
            {this.props.children}
          </span>
          <div className={classnames(styles.submenu, styles.host)}>
            <div className={styles['submenu-inner']}>
              <ul
                className={classnames(
                  styles['submenu-items'],
                  styles['list-unstyled'],
                )}
              >
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20202&o=h&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <Translate value="All" />
                      <span className={styles['submenu-count']}>
                        {numeral(data.total).format()}
                      </span>
                    </div>
                  </Link>
                </li>
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20202&o=h_down&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <span
                        className={classnames(
                          styles['dot-colored'],
                          styles.red,
                        )}
                      >
                        <Translate value="Down" />
                      </span>
                      <span className={styles['submenu-count']}>
                        {numeral(data.down.unhandled).format()}
                        {'/'}
                        {numeral(data.down.total).format()}
                      </span>
                    </div>
                  </Link>
                </li>
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20202&o=h_unreachable&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <span
                        className={classnames(
                          styles['dot-colored'],
                          styles.gray,
                        )}
                      >
                        <Translate value="Unreachable" />
                      </span>
                      <span className={styles['submenu-count']}>
                        {numeral(data.unreachable.unhandled).format()}
                        {'/'}
                        {numeral(data.unreachable.total).format()}
                      </span>
                    </div>
                  </Link>
                </li>
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20202&o=h_up&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <span
                        className={classnames(
                          styles['dot-colored'],
                          styles.green,
                        )}
                      >
                        <Translate value="Up" />
                      </span>
                      <span className={styles['submenu-count']}>
                        {numeral(data.ok).format()}
                      </span>
                    </div>
                  </Link>
                </li>
                <li className={styles['submenu-item']}>
                  <Link
                    to="/main.php?p=20202&o=h_pending&search="
                    className={styles['submenu-item-link']}
                  >
                    <div onClick={this.toggle}>
                      <span
                        className={classnames(
                          styles['dot-colored'],
                          styles.blue,
                        )}
                      >
                        <Translate value="Pending" />
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
)(HostMenu);

HostMenu.propTypes = {
  refreshTime: PropTypes.oneOfType([PropTypes.number, PropTypes.bool])
    .isRequired,
};
