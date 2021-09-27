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
import { withTranslation } from 'react-i18next';

import {
  IconHeader,
  IconNumber,
  IconToggleSubmenu,
  SubmenuHeader,
  SubmenuItem,
  SubmenuItems,
} from '@centreon/ui';

import styles from '../header.scss';
import axios from '../../../axios';
import {
  getHostResourcesUrl,
  downCriterias,
  unreachableCriterias,
  upCriterias,
  pendingCriterias,
  unhandledStateCriterias,
} from '../getResourcesUrl';

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
    const { t } = this.props;

    const { useDeprecatedPages } = this.props;

    const unhandledDownHostsLink = useDeprecatedPages
      ? '/main.php?p=20202&o=h_down&search='
      : getHostResourcesUrl({
          stateCriterias: unhandledStateCriterias,
          statusCriterias: downCriterias,
        });

    const unhandledUnreachableHostsLink = useDeprecatedPages
      ? '/main.php?p=20202&o=h_unreachable&search='
      : getHostResourcesUrl({
          stateCriterias: unhandledStateCriterias,
          statusCriterias: unreachableCriterias,
        });

    const upHostsLink = useDeprecatedPages
      ? '/main.php?p=20202&o=h_up&search='
      : getHostResourcesUrl({
          statusCriterias: upCriterias,
        });

    const hostsLink = useDeprecatedPages
      ? '/main.php?p=20202&o=h&search='
      : getHostResourcesUrl();

    const pendingHostsLink = useDeprecatedPages
      ? '/main.php?p=20202&o=h_pending&search='
      : getHostResourcesUrl({
          statusCriterias: pendingCriterias,
        });

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
            iconName={t('Hosts')}
            iconType="hosts"
            onClick={this.toggle}
          >
            {data.pending > 0 && <span className={styles['custom-icon']} />}
          </IconHeader>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to={unhandledDownHostsLink}
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
            to={unhandledUnreachableHostsLink}
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
            to={upHostsLink}
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
                to={hostsLink}
                onClick={this.toggle}
              >
                <SubmenuItem
                  submenuCount={numeral(data.total).format()}
                  submenuTitle={t('All')}
                />
              </Link>
              <Link
                className={styles.link}
                to={unhandledDownHostsLink}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="red"
                  submenuCount={`${numeral(data.down.unhandled).format(
                    '0a',
                  )}/${numeral(data.down.total).format('0a')}`}
                  submenuTitle={t('Down')}
                />
              </Link>
              <Link
                className={styles.link}
                to={unhandledUnreachableHostsLink}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="gray"
                  submenuCount={`${numeral(data.unreachable.unhandled).format(
                    '0a',
                  )}/${numeral(data.unreachable.total).format('0a')}`}
                  submenuTitle={t('Unreachable')}
                />
              </Link>
              <Link
                className={styles.link}
                to={upHostsLink}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="green"
                  submenuCount={numeral(data.ok).format()}
                  submenuTitle={t('Up')}
                />
              </Link>
              <Link
                className={styles.link}
                to={pendingHostsLink}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="blue"
                  submenuCount={numeral(data.pending).format()}
                  submenuTitle={t('Pending')}
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

export default withTranslation()(
  connect(mapStateToProps, mapDispatchToProps)(HostMenu),
);

HostMenu.propTypes = {
  refreshTime: PropTypes.oneOfType([PropTypes.number, PropTypes.bool])
    .isRequired,
};
