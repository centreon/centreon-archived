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

import PollerIcon from '@material-ui/icons/DeviceHub';
import StorageIcon from '@material-ui/icons/Storage';
import { Typography, Button } from '@material-ui/core';
import LatencyIcon from '@material-ui/icons/Speed';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';
import ExpandLessIcon from '@material-ui/icons/ExpandLess';

import axios from '../../axios';
import styles from '../header.scss';
import { allowedPagesSelector } from '../../redux/selectors/navigation/allowedPages';
import MenuLoader from '../../components/MenuLoader';

import ExportConfiguration from './ExportConfiguration';

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
          style={{
            display: 'flex',
            justifyContent: 'center',
          }}
          title={
            databaseClass === 'green'
              ? t('OK: all database poller updates are active')
              : t(
                  'Some database poller updates are not active; check your configuration',
                )
          }
        >
          <StorageIcon />
        </span>
      </span>
      <span
        className={classnames(
          styles['wrap-left-icon'],
          styles.round,
          styles[latencyClass],
        )}
      >
        <span
          style={{
            display: 'flex',
            justifyContent: 'center',
          }}
          title={
            latencyClass === 'green'
              ? t('OK: no latency detected on your platform')
              : t(
                  'Latency detected, check configuration for better optimization',
                )
          }
        >
          <LatencyIcon />
        </span>
      </span>
    </>
  );
};

class PollerMenu extends Component {
  pollerService = axios(
    'internal.php?object=centreon_topcounter&action=pollersListIssues',
  );

  refreshIntervalRef = null;

  state = {
    data: null,
    intervalApplied: false,
    isExporting: false,
    toggled: false,
  };

  componentDidMount() {
    window.addEventListener('mousedown', this.handleClick, false);
  }

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
    clearInterval(this.refreshIntervalRef);
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
    const { refreshInterval } = nextProps;
    const { intervalApplied } = this.state;
    if (refreshInterval && !intervalApplied) {
      this.getData();
      this.refreshIntervalRef = setInterval(() => {
        this.getData();
      }, refreshInterval * 1000);
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
  };

  redirectsToPollersPage = () => {
    this.closeSubmenu();

    this.props.history.push(
      `/main.php?p=${POLLER_CONFIGURATION_TOPOLOGY_PAGE}`,
    );
  };

  // hide poller detailed data if click outside
  handleClick = (e) => {
    if (
      !this.poller ||
      this.poller.contains(e.target) ||
      this.state.isExporting
    ) {
      return;
    }
    this.setState({
      toggled: false,
    });
  };

  setIsExportingConfiguration = (newIsExporting) => {
    this.setState({
      isExporting: newIsExporting,
    });
  };

  render() {
    const { data, toggled } = this.state;

    if (!data) {
      return <MenuLoader />;
    }

    // check if poller configuration page is allowed
    const { allowedPages, t } = this.props;
    const allowPollerConfiguration = allowedPages.includes(
      POLLER_CONFIGURATION_TOPOLOGY_PAGE,
    );

    const statusIcon = getPollerStatusIcon(t)(data.issues);

    const ExpandPollerMenuIcon = toggled ? ExpandLessIcon : ExpandMoreIcon;

    return (
      <div
        className={classnames(styles['wrap-left-pollers'], {
          [styles['submenu-active']]: toggled,
        })}
        ref={(poller) => (this.poller = poller)}
      >
        <span
          className={classnames(styles['wrap-left-icon'], styles.pollers)}
          onClick={this.toggle}
        >
          <PollerIcon style={{ color: '#FFFFFF' }} />
          <span className={styles['wrap-left-icon__name']}>
            <Typography variant="caption">{t('Pollers')}</Typography>
          </span>
        </span>

        {statusIcon}

        <ExpandPollerMenuIcon
          style={{ color: '#FFFFFF', cursor: 'pointer' }}
          onClick={this.toggle}
        />
        <span>{this.props.children}</span>
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
                  <Typography variant="body2">{t('All pollers')}</Typography>
                  <Typography variant="body2">
                    {data.total ? data.total : '...'}
                  </Typography>
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
                        <span className={styles['submenu-item-link']}>
                          <Typography variant="body2">{message} </Typography>
                          <Typography variant="body2">
                            {issue.total ? issue.total : '...'}
                          </Typography>
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
                                  className={styles['submenu-item-link']}
                                  key={poller.name}
                                >
                                  <span
                                    className={classnames(
                                      styles['dot-colored'],
                                      styles[color],
                                    )}
                                  >
                                    <Typography variant="body2">
                                      {poller.name}
                                    </Typography>
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
                <Button
                  size="small"
                  style={{ marginTop: '8px' }}
                  variant="contained"
                  onClick={this.closeSubmenu}
                >
                  {t('Configure pollers')}
                </Button>
              )}
            </ul>
            <ExportConfiguration
              setIsExportingConfiguration={this.setIsExportingConfiguration}
              total={data.total}
            />
          </div>
          <div className={styles['submenu-padding']} />
        </div>
      </div>
    );
  }
}

const mapStateToProps = (state) => ({
  allowedPages: allowedPagesSelector(state),
});

const mapDispatchToProps = {};

export default withRouter(
  withTranslation()(connect(mapStateToProps, mapDispatchToProps)(PollerMenu)),
);

PollerMenu.propTypes = {
  allowedPages: PropTypes.arrayOf(PropTypes.string),
  refreshInterval: PropTypes.number.isRequired,
};

PollerMenu.defaultProps = {
  allowedPages: [],
};
