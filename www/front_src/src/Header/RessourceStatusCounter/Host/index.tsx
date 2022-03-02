/* eslint-disable @typescript-eslint/naming-convention */
import React from 'react';

import classnames from 'classnames';
import * as yup from 'yup';
import numeral from 'numeral';
import { Link } from 'react-router-dom';
import { useTranslation, withTranslation } from 'react-i18next';

import HostIcon from '@material-ui/icons/Dns';

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
  getHostResourcesUrl,
  downCriterias,
  unreachableCriterias,
  upCriterias,
  pendingCriterias,
  unhandledStateCriterias,
} from '../getResourcesUrl';
import RessourceStatusCounter, { useStyles } from '..';

const hostStatusEndpoint =
  'internal.php?object=centreon_topcounter&action=hosts_status';

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

interface HostData {
  down: {
    total: number;
    unhandled: number;
  };
  ok: number;
  pending: number;
  total: number;
  unandled: number;
  unreachable: {
    total: number;
    unhandled: number;
  };
}

const HostStatusCounter = (): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const { use_deprecated_pages } = useUserContext();

  const unhandledDownHostsLink = use_deprecated_pages
    ? '/main.php?p=20202&o=h_down&search='
    : getHostResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: downCriterias,
      });

  const unhandledUnreachableHostsLink = use_deprecated_pages
    ? '/main.php?p=20202&o=h_unreachable&search='
    : getHostResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: unreachableCriterias,
      });

  const upHostsLink = use_deprecated_pages
    ? '/main.php?p=20202&o=h_up&search='
    : getHostResourcesUrl({
        statusCriterias: upCriterias,
      });

  const hostsLink = use_deprecated_pages
    ? '/main.php?p=20202&o=h&search='
    : getHostResourcesUrl();

  const pendingHostsLink = use_deprecated_pages
    ? '/main.php?p=20202&o=h_pending&search='
    : getHostResourcesUrl({
        statusCriterias: pendingCriterias,
      });

  return (
    <RessourceStatusCounter<HostData>
      endpoint={hostStatusEndpoint}
      loaderWidth={27}
      schema={statusSchema}
    >
      {({ hasPending, toggled, toggleDetailedView, data }): JSX.Element => (
        <div className={`${styles.wrapper} wrap-right-hosts`}>
          <SubmenuHeader active={toggled}>
            <IconHeader
              Icon={HostIcon}
              iconName={t('Hosts')}
              pending={hasPending}
              onClick={toggleDetailedView}
            />
            <Link
              className={classnames(classes.link, styles['wrap-middle-icon'])}
              data-testid="Hosts Down"
              to={unhandledDownHostsLink}
            >
              <StatusCounter
                count={data.down.unhandled}
                severityCode={SeverityCode.High}
              />
            </Link>
            <Link
              className={classnames(classes.link, styles['wrap-middle-icon'])}
              data-testid="Hosts Unreachable"
              to={unhandledUnreachableHostsLink}
            >
              <StatusCounter
                count={data.unreachable.unhandled}
                severityCode={SeverityCode.Low}
              />
            </Link>
            <Link
              className={classnames(classes.link, styles['wrap-middle-icon'])}
              data-testid="Hosts Up"
              to={upHostsLink}
            >
              <StatusCounter count={data.ok} severityCode={SeverityCode.Ok} />
            </Link>
            <IconToggleSubmenu
              data-testid="submenu-hosts"
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
                  to={hostsLink}
                  onClick={toggleDetailedView}
                >
                  <SubmenuItem
                    countTestId="submenu hosts count all"
                    submenuCount={numeral(data.total).format()}
                    submenuTitle={t('All')}
                    titleTestId="submenu hosts title all"
                  />
                </Link>
                <Link
                  className={classes.link}
                  to={unhandledDownHostsLink}
                  onClick={toggleDetailedView}
                >
                  <SubmenuItem
                    countTestId="submenu hosts count down"
                    dotColored="red"
                    submenuCount={`${numeral(data.down.unhandled).format(
                      '0a',
                    )}/${numeral(data.down.total).format('0a')}`}
                    submenuTitle={t('Down')}
                    titleTestId="submenu hosts title down"
                  />
                </Link>
                <Link
                  className={classes.link}
                  to={unhandledUnreachableHostsLink}
                  onClick={toggleDetailedView}
                >
                  <SubmenuItem
                    countTestId="submenu hosts count unreachable"
                    dotColored="gray"
                    submenuCount={`${numeral(data.unreachable.unhandled).format(
                      '0a',
                    )}/${numeral(data.unreachable.total).format('0a')}`}
                    submenuTitle={t('Unreachable')}
                    titleTestId="submenu hosts title unreachable"
                  />
                </Link>
                <Link
                  className={classes.link}
                  to={upHostsLink}
                  onClick={toggleDetailedView}
                >
                  <SubmenuItem
                    countTestId="submenu hosts count ok"
                    dotColored="green"
                    submenuCount={numeral(data.ok).format()}
                    submenuTitle={t('Up')}
                    titleTestId="submenu hosts title ok"
                  />
                </Link>
                <Link
                  className={classes.link}
                  to={pendingHostsLink}
                  onClick={toggleDetailedView}
                >
                  <SubmenuItem
                    countTestId="submenu hosts count pending"
                    dotColored="blue"
                    submenuCount={numeral(data.pending).format()}
                    submenuTitle={t('Pending')}
                    titleTestId="submenu hosts title pending"
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

export default withTranslation()(HostStatusCounter);
