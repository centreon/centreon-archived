/* eslint-disable @typescript-eslint/naming-convention */

import React from 'react';

import classnames from 'classnames';
import * as yup from 'yup';
import numeral from 'numeral';
import { Link } from 'react-router-dom';
import { useTranslation, withTranslation } from 'react-i18next';

import ServiceIcon from '@mui/icons-material/Grain';

import {
  IconHeader,
  IconToggleSubmenu,
  SubmenuHeader,
  SubmenuItem,
  SubmenuItems,
  SeverityCode,
  StatusCounter,
} from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import styles from '../../header.scss';
import {
  getServiceResourcesUrl,
  criticalCriterias,
  warningCriterias,
  unknownCriterias,
  okCriterias,
  pendingCriterias,
  unhandledStateCriterias,
} from '../getResourcesUrl';
import RessourceStatusCounter, { useStyles } from '..';

const serviceStatusEndpoint =
  'internal.php?object=centreon_topcounter&action=servicesStatus';

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

const ServiceStatusCounter = (): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const { use_deprecated_pages } = useUserContext();

  const unhandledCriticalServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc_unhandled&statusFilter=critical&search='
    : getServiceResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: criticalCriterias,
      });

  const unhandledWarningServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc_unhandled&statusFilter=warning&search='
    : getServiceResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: warningCriterias,
      });

  const unhandledUnknownServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc_unhandled&statusFilter=unknown&search='
    : getServiceResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: unknownCriterias,
      });

  const okServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc&statusFilter=ok&search='
    : getServiceResourcesUrl({ statusCriterias: okCriterias });

  const servicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc&statusFilter=&search='
    : getServiceResourcesUrl();

  const pendingServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc&statusFilter=&search='
    : getServiceResourcesUrl({
        statusCriterias: pendingCriterias,
      });

  return (
    <RessourceStatusCounter
      endpoint={serviceStatusEndpoint}
      loaderWidth={33}
      schema={statusSchema}
    >
      {({ hasPending, data, toggled, toggleDetailedView }): JSX.Element => (
        <div className={`${styles.wrapper} wrap-right-services`}>
          <SubmenuHeader active={toggled}>
            <IconHeader
              Icon={ServiceIcon}
              iconName={t('Services')}
              pending={hasPending}
              onClick={toggleDetailedView}
            />
            <Link
              className={classnames(classes.link, styles['wrap-middle-icon'])}
              to={unhandledCriticalServicesLink}
            >
              <StatusCounter
                count={data.critical.unhandled}
                severityCode={SeverityCode.High}
              />
            </Link>
            <Link
              className={classnames(classes.link, styles['wrap-middle-icon'])}
              to={unhandledWarningServicesLink}
            >
              <StatusCounter
                count={data.warning.unhandled}
                severityCode={SeverityCode.Medium}
              />
            </Link>
            <Link
              className={classnames(classes.link, styles['wrap-middle-icon'])}
              to={unhandledUnknownServicesLink}
            >
              <StatusCounter
                count={data.unknown.unhandled}
                severityCode={SeverityCode.Low}
              />
            </Link>
            <Link
              className={classnames(classes.link, styles['wrap-middle-icon'])}
              to={okServicesLink}
            >
              <StatusCounter count={data.ok} severityCode={SeverityCode.Ok} />
            </Link>
            <IconToggleSubmenu
              iconType="arrow"
              rotate={toggled}
              onClick={toggleDetailedView}
            />
            <div
              className={classnames(styles['submenu-toggle'], {
                [styles['submenu-toggle-active'] as string]: toggled,
              })}
            >
              <SubmenuItems>
                <Link
                  className={classes.link}
                  to={servicesLink}
                  onClick={toggleDetailedView}
                >
                  <SubmenuItem
                    submenuCount={numeral(data.total).format()}
                    submenuTitle={t('All')}
                  />
                </Link>
                <Link
                  className={classes.link}
                  to={unhandledCriticalServicesLink}
                  onClick={toggleDetailedView}
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
                  className={classes.link}
                  to={unhandledWarningServicesLink}
                  onClick={toggleDetailedView}
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
                  className={classes.link}
                  to={unhandledUnknownServicesLink}
                  onClick={toggleDetailedView}
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
                  className={classes.link}
                  to={okServicesLink}
                  onClick={toggleDetailedView}
                >
                  <SubmenuItem
                    dotColored="green"
                    submenuCount={numeral(data.ok).format()}
                    submenuTitle={t('Ok')}
                  />
                </Link>
                <Link
                  className={classes.link}
                  to={pendingServicesLink}
                  onClick={toggleDetailedView}
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
      )}
    </RessourceStatusCounter>
  );
};

export default withTranslation()(ServiceStatusCounter);
