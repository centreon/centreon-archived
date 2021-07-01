/* eslint-disable react/jsx-key */
/* eslint-disable camelcase */
/* eslint-disable react/no-unused-prop-types */
/* eslint-disable react/forbid-prop-types */
/* eslint-disable radix */
/* eslint-disable react/button-has-type */
/* eslint-disable react/prop-types */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable no-return-assign */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-nested-ternary */
/* eslint-disable import/no-extraneous-dependencies */

import React, { Component } from 'react';

import PropTypes from 'prop-types';
import classnames from 'classnames';
import { withTranslation } from 'react-i18next';
import { withRouter } from 'react-router-dom';
import { connect } from 'react-redux';

import axios from '../../../axios';
import styles from '../header.scss';
import { allowedPagesSelector } from '../../../redux/selectors/navigation/allowedPages';

const POLLER_CONFIGURATION_TOPOLOGY_PAGE = '60901';

const getIssueClass = (issues, key) => {
  return issues && issues.length !== 0
    ? issues[key]
      ? issues[key].warning
        ? 'orange'
        : issues[key].critical
        ? 'red'
        : 'green'
      : 'green'
    : 'green';
};

const getPollerStatusIcon = (t) => (issues) => {
  const databaseClass = getIssueClass(issues, 'database');

  const latencyClass = getIssueClass(issues, 'latency');

  return (
    <>
      <span
        className={classnames(
          styles['wrap-left-icon'],
          styles.round,
          styles[databaseClass],
        )}
      >
        <span
          className={classnames(styles.iconmoon, styles['icon-database'])}
          title={
            databaseClass === 'green'
              ? t('OK: all database poller updates are active')
              : t(
                  'Some database poller updates are not active; check your configuration',
                )
          }
        />
      </span>
      <span
        className={classnames(
          styles['wrap-left-icon'],
          styles.round,
          styles[latencyClass],
        )}
      >
        <span
          className={classnames(styles.iconmoon, styles['icon-clock'])}
          title={
            latencyClass === 'green'
              ? t('OK: no latency detected on your platform')
              : t(
                  'Latency detected, check configuration for better optimization',
                )
          }
        />
      </span>
    </>
  );
};

class PollerMenu extends Component {
  pollerService = axios(
    'internal.php?object=centreon_topcounter&action=pollersListIssues',
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
    this.pollerService
      .get()
      .then(({ data }) => {
        this.setState({
          data,
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

  // display/hide detailed poller data
  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled,
    });
  };

  closeSubmenu = () => {
    this.setState({
      toggled: false,
    });

    this.props.history.push(
      `/main.php?p=${POLLER_CONFIGURATION_TOPOLOGY_PAGE}`,
    );
  };

  // hide poller detailed data if click outside
  handleClick = (e) => {
    if (!this.poller || this.poller.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false,
    });
  };

  render() {
    const { data, toggled } = this.state;

    if (!data) {
      return null;
    }

    // check if poller configuration page is allowed
    const { allowedPages, t } = this.props;
    const allowPollerConfiguration = allowedPages.includes(
      POLLER_CONFIGURATION_TOPOLOGY_PAGE,
    );

    const statusIcon = getPollerStatusIcon(t)(data.issues);

    return (
      <div
        className={classnames(styles['wrap-left-pollers'], {
          [styles['submenu-active']]: toggled,
        })}
        ref={(poller) => (this.poller = poller)}
      >
        {statusIcon}
        <div>
          <span
            className={classnames(styles['wrap-left-icon'], styles.pollers)}
            onClick={this.toggle}
          >
            <span
              className={classnames(styles.iconmoon, styles['icon-poller'])}
            />
            <span className={styles['wrap-left-icon__name']}>
              {t('Pollers')}
            </span>
          </span>
          <span
            className={styles['toggle-submenu-arrow']}
            onClick={this.toggle}
          >
            {this.props.children}
          </span>
        </div>
        <div className={classnames(styles.submenu, styles.pollers)}>
          <div className={styles['submenu-content']}>
            <ul
              className={classnames(
                styles['submenu-items'],
                styles['list-unstyled'],
              )}
            >
              <li className={styles['submenu-item']}>
                <span className={styles['submenu-item-link']}>
                  {t('All pollers')}
                  <span className={styles['submenu-count']}>
                    {data.total ? data.total : '...'}
                  </span>
                </span>
              </li>
              {data.issues
                ? Object.entries(data.issues).map(([key, issue]) => {
                    let message = '';

                    if (key === 'database') {
                      message = t('Database updates not active');
                    } else if (key === 'stability') {
                      message = t('Pollers not running');
                    } else if (key === 'latency') {
                      message = t('Latency detected');
                    }

                    return (
                      <li className={styles['submenu-top-item']} key={key}>
                        <span className={styles['submenu-top-item-link']}>
                          {message}
                          <span className={styles['submenu-top-count']}>
                            {issue.total ? issue.total : '...'}
                          </span>
                        </span>
                        {Object.entries(issue).map(([elem, values]) => {
                          if (values.poller) {
                            const pollers = values.poller;
                            return pollers.map((poller) => {
                              let color = 'red';
                              if (elem === 'warning') {
                                color = 'orange';
                              }
                              return (
                                <span
                                  className={styles['submenu-top-item-link']}
                                  key={poller.name}
                                  style={{ padding: '0px 16px 17px' }}
                                >
                                  <span
                                    className={classnames(
                                      styles['dot-colored'],
                                      styles[color],
                                    )}
                                  >
                                    {poller.name}
                                  </span>
                                </span>
                              );
                            });
                          }
                          return null;
                        })}
                      </li>
                    );
                  })
                : null}
              {allowPollerConfiguration /* display poller configuration button if user is allowed */ && (
                <button
                  className={classnames(
                    styles.btn,
                    styles['btn-big'],
                    styles['btn-green'],
                    styles['submenu-top-button'],
                  )}
                  onClick={this.closeSubmenu}
                >
                  {t('Configure pollers')}
                </button>
              )}
            </ul>
          </div>
          <div className={styles['submenu-padding']} />
        </div>
      </div>
    );
  }
}

const mapStateToProps = (state) => ({
  allowedPages: allowedPagesSelector(state),
  refreshTime: state.intervals
    ? parseInt(state.intervals.AjaxTimeReloadStatistic) * 1000
    : false,
});

const mapDispatchToProps = {};

export default withRouter(
  withTranslation()(connect(mapStateToProps, mapDispatchToProps)(PollerMenu)),
);

PollerMenu.propTypes = {
  allowedPages: PropTypes.arrayOf(PropTypes.string).isRequired,
  refreshTime: PropTypes.oneOfType([PropTypes.number, PropTypes.bool])
    .isRequired,
};
