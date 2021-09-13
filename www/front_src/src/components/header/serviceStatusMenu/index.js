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

import ServiceIcon from '@material-ui/icons/RoomService';

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
    const { t } = this.props;

    // do not display service information until having data
    if (!data) {
      return <MenuLoader width={33} />;
    }

    const hasPendingServices = data.pending > 0;

    return (
      <div
        className={`${styles.wrapper} wrap-right-services`}
        ref={(service) => (this.service = service)}
      >
        <SubmenuHeader active={toggled} submenuType="top">
          <IconHeader
            Icon={ServiceIcon}
            iconName={t('Services')}
            pending={hasPendingServices}
            onClick={this.toggle}
          />
          <Link
            className={classnames(styles.link, styles['wrap-middle-icon'])}
            to={getServiceResourcesUrl({
              stateCriterias: unhandledStateCriterias,
              statusCriterias: criticalCriterias,
            })}
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
            to={getServiceResourcesUrl({
              stateCriterias: unhandledStateCriterias,
              statusCriterias: warningCriterias,
            })}
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
            to={getServiceResourcesUrl({
              stateCriterias: unhandledStateCriterias,
              statusCriterias: unknownCriterias,
            })}
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
            to={getServiceResourcesUrl({ statusCriterias: okCriterias })}
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
                to={getServiceResourcesUrl()}
                onClick={this.toggle}
              >
                <SubmenuItem
                  submenuCount={numeral(data.total).format()}
                  submenuTitle={t('All')}
                />
              </Link>
              <Link
                className={styles.link}
                to={getServiceResourcesUrl({
                  stateCriterias: unhandledStateCriterias,
                  statusCriterias: criticalCriterias,
                })}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="red"
                  submenuCount={`${numeral(
                    data.critical.unhandled,
                  ).format()}/${numeral(data.critical.total).format()}`}
                  submenuTitle={t('Critical')}
                />
              </Link>
              <Link
                className={styles.link}
                to={getServiceResourcesUrl({
                  stateCriterias: unhandledStateCriterias,
                  statusCriterias: warningCriterias,
                })}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="orange"
                  submenuCount={`${numeral(
                    data.warning.unhandled,
                  ).format()}/${numeral(data.warning.total).format()}`}
                  submenuTitle={t('Warning')}
                />
              </Link>
              <Link
                className={styles.link}
                to={getServiceResourcesUrl({
                  stateCriterias: unhandledStateCriterias,
                  statusCriterias: unknownCriterias,
                })}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="gray"
                  submenuCount={`${numeral(
                    data.unknown.unhandled,
                  ).format()}/${numeral(data.unknown.total).format()}`}
                  submenuTitle={t('Unknown')}
                />
              </Link>
              <Link
                className={styles.link}
                to={getServiceResourcesUrl({ statusCriterias: okCriterias })}
                onClick={this.toggle}
              >
                <SubmenuItem
                  dotColored="green"
                  submenuCount={numeral(data.ok).format()}
                  submenuTitle={t('Ok')}
                />
              </Link>
              <Link
                className={styles.link}
                to={getServiceResourcesUrl({
                  statusCriterias: pendingCriterias,
                })}
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
