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
  getServiceResourcesUrl,
  criticalCriterias,
  warningCriterias,
  unknownCriterias,
  okCriterias,
  pendingCriterias,
  unhandledStateCriterias,
} from '../getResourcesUrl';
import MenuLoader from '../../MenuLoader';

const numberFormat = yup.number().required().integer();

const statusSchema = yup.object().shape({
  critical: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat,
  }),
  ok: numberFormat,
  pending: numberFormat,
  refreshTime: numberFormat,
  total: numberFormat,
  unknown: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat,
  }),
  warning: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat,
  }),
});

class ServiceStatusMenu extends Component {
  servicesStatusService = axios(
    'internal.php?object=centreon_topcounter&action=servicesStatus',
  );

  refreshInterval = null;

  state = {
    allowed: true,
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
            allowed: false,
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
    const { data, toggled, allowed } = this.state;
    const { t, useDeprecatedPages } = this.props;

    const unhandledCriticalServicesLink = useDeprecatedPages
      ? '/main.php?p=20201&o=svc_unhandled&statusFilter=critical&search='
      : getServiceResourcesUrl({
          stateCriterias: unhandledStateCriterias,
          statusCriterias: criticalCriterias,
        });

    const unhandledWarningServicesLink = useDeprecatedPages
      ? '/main.php?p=20201&o=svc_unhandled&statusFilter=warning&search='
      : getServiceResourcesUrl({
          stateCriterias: unhandledStateCriterias,
          statusCriterias: warningCriterias,
        });

    const unhandledUnknownServicesLink = useDeprecatedPages
      ? '/main.php?p=20201&o=svc_unhandled&statusFilter=unknown&search='
      : getServiceResourcesUrl({
          stateCriterias: unhandledStateCriterias,
          statusCriterias: unknownCriterias,
        });

    const okServicesLink = useDeprecatedPages
      ? '/main.php?p=20201&o=svc&statusFilter=ok&search='
      : getServiceResourcesUrl({ statusCriterias: okCriterias });

    const servicesLink = useDeprecatedPages
      ? '/main.php?p=20201&o=svc&statusFilter=&search='
      : getServiceResourcesUrl();

    const pendingServicesLink = useDeprecatedPages
      ? '/main.php?p=20201&o=svc&statusFilter=&search='
      : getServiceResourcesUrl({
          statusCriterias: pendingCriterias,
        });

    // do not display skeleton if user is not allowed to display top counter
    if (!allowed) {
      return null;
    }

    // do not display service information until having data
    if (!data) {
      return <MenuLoader width={33} />;
    }

    return (
      <div
        className={`${styles.wrapper} wrap-right-services`}
        ref={(service) => (this.service = service)}
      >
        <SubmenuHeader active={toggled} submenuType="top">
          <IconHeader
            iconName={t('Services')}
            iconType="services"
            onClick={this.toggle}
          >
            {data.pending > 0 && <span className={styles['custom-icon']} />}
          </IconHeader>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            data-testid="Services Critical"
            to={unhandledCriticalServicesLink}
          >
            <IconNumber
              iconColor="red"
              iconNumber={
                <span id="count-svc-critical">
                  {numeral(data.critical.unhandled).format('0a')}
                </span>
              }
              iconType={`${
                data.critical.unhandled > 0 ? 'colored' : 'bordered'
              }`}
            />
          </Link>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            data-testid="Services Warning"
            to={unhandledWarningServicesLink}
          >
            <IconNumber
              iconColor="orange"
              iconNumber={
                <span id="count-svc-warning">
                  {numeral(data.warning.unhandled).format('0a')}
                </span>
              }
              iconType={`${
                data.warning.unhandled > 0 ? 'colored' : 'bordered'
              }`}
            />
          </Link>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            data-testid="Services Unknown"
            to={unhandledUnknownServicesLink}
          >
            <IconNumber
              iconColor="gray-light"
              iconNumber={
                <span id="count-svc-unknown">
                  {numeral(data.unknown.unhandled).format('0a')}
                </span>
              }
              iconType={`${
                data.unknown.unhandled > 0 ? 'colored' : 'bordered'
              }`}
            />
          </Link>
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            data-testid="Services Ok"
            to={okServicesLink}
          >
            <IconNumber
              iconColor="green"
              iconNumber={
                <span id="count-svc-ok">{numeral(data.ok).format('0a')}</span>
              }
              iconType={`${data.ok > 0 ? 'colored' : 'bordered'}`}
            />
          </Link>
          <IconToggleSubmenu
            data-testid="submenu-service"
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
                to={servicesLink}
                onClick={this.toggle}
              >
                <SubmenuItem
                  countTestId="submenu services count all"
                  submenuCount={numeral(data.total).format()}
                  submenuTitle={t('All')}
                  titleTestId="submenu services title all"
                />
              </Link>
              <Link
                className={styles.link}
                to={unhandledCriticalServicesLink}
                onClick={this.toggle}
              >
                <SubmenuItem
                  countTestId="submenu services count critical"
                  dotColored="red"
                  submenuCount={`${numeral(
                    data.critical.unhandled,
                  ).format()}/${numeral(data.critical.total).format()}`}
                  submenuTitle={t('Critical')}
                  titleTestId="submenu services title critical"
                />
              </Link>
              <Link
                className={styles.link}
                to={unhandledWarningServicesLink}
                onClick={this.toggle}
              >
                <SubmenuItem
                  countTestId="submenu services count warning"
                  dotColored="orange"
                  submenuCount={`${numeral(
                    data.warning.unhandled,
                  ).format()}/${numeral(data.warning.total).format()}`}
                  submenuTitle={t('Warning')}
                  titleTestId="submenu services title warning"
                />
              </Link>
              <Link
                className={styles.link}
                to={unhandledUnknownServicesLink}
                onClick={this.toggle}
              >
                <SubmenuItem
                  countTestId="submenu services count unknown"
                  dotColored="gray"
                  submenuCount={`${numeral(
                    data.unknown.unhandled,
                  ).format()}/${numeral(data.unknown.total).format()}`}
                  submenuTitle={t('Unknown')}
                  titleTestId="submenu services title unknown"
                />
              </Link>
              <Link
                className={styles.link}
                to={okServicesLink}
                onClick={this.toggle}
              >
                <SubmenuItem
                  countTestId="submenu services count ok"
                  dotColored="green"
                  submenuCount={numeral(data.ok).format()}
                  submenuTitle={t('Ok')}
                  titleTestId="submenu services title ok"
                />
              </Link>
              <Link
                className={styles.link}
                to={pendingServicesLink}
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
  connect(mapStateToProps, mapDispatchToProps)(ServiceStatusMenu),
);

ServiceStatusMenu.propTypes = {
  refreshTime: PropTypes.oneOfType([PropTypes.number, PropTypes.bool])
    .isRequired,
};
