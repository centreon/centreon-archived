import clsx from 'clsx';
import * as yup from 'yup';
import numeral from 'numeral';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation, withTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import ServiceIcon from '@mui/icons-material/Grain';

import {
  IconHeader,
  IconToggleSubmenu,
  SubmenuHeader,
  SubmenuItem,
  SubmenuItems,
  SeverityCode,
  StatusCounter,
  SelectEntry,
} from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import getDefaultCriterias from '../../../Resources/Filter/Criterias/default';
import { applyFilterDerivedAtom } from '../../../Resources/Filter/filterAtoms';
import {
  getServiceResourcesUrl,
  criticalCriterias,
  warningCriterias,
  unknownCriterias,
  okCriterias,
  pendingCriterias,
  unhandledStateCriterias,
  serviceCriteria,
} from '../getResourcesUrl';
import RessourceStatusCounter, { useStyles } from '..';
import { Criteria } from '../../../Resources/Filter/Criterias/models';

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

interface SelectResourceProps {
  criterias: Array<Criteria>;
  link: string;
  toggle?: () => void;
}

const ServiceStatusCounter = (): JSX.Element => {
  const classes = useStyles();
  const navigate = useNavigate();

  const { t } = useTranslation();

  const { use_deprecated_pages } = useAtomValue(userAtom);
  const applyFilter = useUpdateAtom(applyFilterDerivedAtom);

  const unhandledCriticalServicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
    states: unhandledStateCriterias.value,
    statuses: criticalCriterias.value as Array<SelectEntry>,
  });
  const unhandledCriticalServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc_unhandled&statusFilter=critical&search='
    : getServiceResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: criticalCriterias,
      });

  const unhandledWarningServicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
    states: unhandledStateCriterias.value,
    statuses: warningCriterias.value as Array<SelectEntry>,
  });
  const unhandledWarningServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc_unhandled&statusFilter=warning&search='
    : getServiceResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: warningCriterias,
      });

  const unhandledUnknownServicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
    states: unhandledStateCriterias.value,
    statuses: unknownCriterias.value as Array<SelectEntry>,
  });
  const unhandledUnknownServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc_unhandled&statusFilter=unknown&search='
    : getServiceResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: unknownCriterias,
      });

  const okServicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
    statuses: okCriterias.value as Array<SelectEntry>,
  });
  const okServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc&statusFilter=ok&search='
    : getServiceResourcesUrl({ statusCriterias: okCriterias });

  const servicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
  });
  const servicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc&statusFilter=&search='
    : getServiceResourcesUrl();

  const pendingServicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
    statuses: pendingCriterias.value as Array<SelectEntry>,
  });
  const pendingServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc&statusFilter=&search='
    : getServiceResourcesUrl({
        statusCriterias: pendingCriterias,
      });

  const changeFilterAndNavigate =
    ({ link, criterias, toggle }: SelectResourceProps) =>
    (e): void => {
      e.preventDefault();
      toggle?.();
      if (!use_deprecated_pages) {
        applyFilter({ criterias, id: '', name: 'New Filter' });
      }
      navigate(link);
    };

  return (
    <RessourceStatusCounter
      endpoint={serviceStatusEndpoint}
      loaderWidth="80%"
      schema={statusSchema}
    >
      {({ hasPending, data, toggled, toggleDetailedView }): JSX.Element => (
        <div>
          <SubmenuHeader active={toggled}>
            <IconHeader
              Icon={ServiceIcon}
              iconName={t('Services')}
              pending={hasPending}
              onClick={toggleDetailedView}
            />
            <Link
              className={clsx(classes.link, classes.wrapMiddleIcon)}
              data-testid="Services Critical"
              to={unhandledCriticalServicesLink}
              onClick={changeFilterAndNavigate({
                criterias: unhandledCriticalServicesCriterias,
                link: unhandledCriticalServicesLink,
              })}
            >
              <StatusCounter
                count={data.critical.unhandled}
                severityCode={SeverityCode.High}
              />
            </Link>
            <Link
              className={clsx(classes.link, classes.wrapMiddleIcon)}
              data-testid="Services Warning"
              to={unhandledWarningServicesLink}
              onClick={changeFilterAndNavigate({
                criterias: unhandledWarningServicesCriterias,
                link: unhandledWarningServicesLink,
              })}
            >
              <StatusCounter
                count={data.warning.unhandled}
                severityCode={SeverityCode.Medium}
              />
            </Link>
            <Link
              className={clsx(classes.link, classes.wrapMiddleIcon)}
              data-testid="Services Unknown"
              to={unhandledUnknownServicesLink}
              onClick={changeFilterAndNavigate({
                criterias: unhandledUnknownServicesCriterias,
                link: unhandledUnknownServicesLink,
              })}
            >
              <StatusCounter
                count={data.unknown.unhandled}
                severityCode={SeverityCode.Low}
              />
            </Link>
            <Link
              className={clsx(classes.link, classes.wrapMiddleIcon)}
              data-testid="Services Ok"
              to={okServicesLink}
              onClick={changeFilterAndNavigate({
                criterias: okServicesCriterias,
                link: okServicesLink,
              })}
            >
              <StatusCounter count={data.ok} severityCode={SeverityCode.Ok} />
            </Link>
            <IconToggleSubmenu
              data-testid="submenu-service"
              iconType="arrow"
              rotate={toggled}
              onClick={toggleDetailedView}
            />
            <div
              className={clsx(classes.subMenuToggle, {
                [classes.subMenuToggleActive]: toggled,
              })}
            >
              <SubmenuItems>
                <Link
                  className={classes.link}
                  data-testid="Services Warning"
                  to={servicesLink}
                  onClick={changeFilterAndNavigate({
                    criterias: servicesCriterias,
                    link: servicesLink,
                    toggle: toggleDetailedView,
                  })}
                >
                  <SubmenuItem
                    countTestId="submenu services count all"
                    submenuCount={numeral(data.total).format()}
                    submenuTitle={t('All')}
                    titleTestId="submenu services title all"
                  />
                </Link>
                <Link
                  className={classes.link}
                  to={unhandledCriticalServicesLink}
                  onClick={changeFilterAndNavigate({
                    criterias: unhandledCriticalServicesCriterias,
                    link: unhandledCriticalServicesLink,
                    toggle: toggleDetailedView,
                  })}
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
                  className={classes.link}
                  to={unhandledWarningServicesLink}
                  onClick={changeFilterAndNavigate({
                    criterias: unhandledWarningServicesCriterias,
                    link: unhandledWarningServicesLink,
                    toggle: toggleDetailedView,
                  })}
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
                  className={classes.link}
                  to={unhandledUnknownServicesLink}
                  onClick={changeFilterAndNavigate({
                    criterias: unhandledUnknownServicesCriterias,
                    link: unhandledUnknownServicesLink,
                    toggle: toggleDetailedView,
                  })}
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
                  className={classes.link}
                  to={okServicesLink}
                  onClick={changeFilterAndNavigate({
                    criterias: okServicesCriterias,
                    link: okServicesLink,
                    toggle: toggleDetailedView,
                  })}
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
                  className={classes.link}
                  to={pendingServicesLink}
                  onClick={changeFilterAndNavigate({
                    criterias: pendingServicesCriterias,
                    link: pendingServicesLink,
                    toggle: toggleDetailedView,
                  })}
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
