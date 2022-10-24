import * as yup from 'yup';
import numeral from 'numeral';
import { useNavigate } from 'react-router-dom';
import { useTranslation, withTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import ServiceIcon from '@mui/icons-material/Grain';

import { SubmenuHeader, SeverityCode, SelectEntry } from '@centreon/ui';
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
  serviceCriteria
} from '../getResourcesUrl';
import RessourceStatusCounter from '..';
import { Criteria } from '../../../Resources/Filter/Criterias/models';

const serviceStatusEndpoint =
  'internal.php?object=centreon_topcounter&action=servicesStatus';

const numberFormat = yup.number().required().integer();

const statusSchema = yup.object().shape({
  critical: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat
  }),
  ok: numberFormat,
  pending: numberFormat,
  refreshTime: numberFormat,
  total: numberFormat,
  unknown: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat
  }),
  warning: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat
  })
});

interface SelectResourceProps {
  criterias: Array<Criteria>;
  link: string;
  toggle?: () => void;
}

const ServiceStatusCounter = (): JSX.Element => {
  const navigate = useNavigate();

  const { t } = useTranslation();

  const { use_deprecated_pages } = useAtomValue(userAtom);
  const applyFilter = useUpdateAtom(applyFilterDerivedAtom);

  const unhandledCriticalServicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
    states: unhandledStateCriterias.value,
    statuses: criticalCriterias.value as Array<SelectEntry>
  });
  const unhandledCriticalServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc_unhandled&statusFilter=critical&search='
    : getServiceResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: criticalCriterias
      });

  const unhandledWarningServicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
    states: unhandledStateCriterias.value,
    statuses: warningCriterias.value as Array<SelectEntry>
  });
  const unhandledWarningServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc_unhandled&statusFilter=warning&search='
    : getServiceResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: warningCriterias
      });

  const unhandledUnknownServicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
    states: unhandledStateCriterias.value,
    statuses: unknownCriterias.value as Array<SelectEntry>
  });
  const unhandledUnknownServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc_unhandled&statusFilter=unknown&search='
    : getServiceResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: unknownCriterias
      });

  const okServicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
    statuses: okCriterias.value as Array<SelectEntry>
  });
  const okServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc&statusFilter=ok&search='
    : getServiceResourcesUrl({ statusCriterias: okCriterias });

  const servicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value
  });
  const servicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc&statusFilter=&search='
    : getServiceResourcesUrl();

  const pendingServicesCriterias = getDefaultCriterias({
    resourceTypes: serviceCriteria.value,
    statuses: pendingCriterias.value as Array<SelectEntry>
  });
  const pendingServicesLink = use_deprecated_pages
    ? '/main.php?p=20201&o=svc&statusFilter=&search='
    : getServiceResourcesUrl({
        statusCriterias: pendingCriterias
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
      loaderWidth={14}
      schema={statusSchema}
    >
      {({ hasPending, data, toggled, toggleDetailedView }): JSX.Element => (
        <SubmenuHeader
          active={toggled}
          counterRightTranslation={1}
          counters={[
            {
              count: data.critical.unhandled,
              onClick: changeFilterAndNavigate({
                criterias: unhandledCriticalServicesCriterias,
                link: unhandledCriticalServicesLink
              }),
              severityCode: SeverityCode.High,
              testId: 'Services Critical',
              to: unhandledCriticalServicesLink
            },

            {
              count: data.warning.unhandled,
              onClick: changeFilterAndNavigate({
                criterias: unhandledWarningServicesCriterias,
                link: unhandledWarningServicesLink
              }),
              severityCode: SeverityCode.Medium,
              testId: 'Services Warning',
              to: unhandledWarningServicesLink
            },

            {
              count: data.unknown.unhandled,
              onClick: changeFilterAndNavigate({
                criterias: unhandledUnknownServicesCriterias,
                link: unhandledUnknownServicesLink
              }),
              severityCode: SeverityCode.Low,
              testId: 'Services Unknown',
              to: unhandledUnknownServicesLink
            },

            {
              count: data.ok,
              onClick: changeFilterAndNavigate({
                criterias: okServicesCriterias,
                link: okServicesLink
              }),
              severityCode: SeverityCode.Ok,
              testId: 'Services Ok',
              to: okServicesLink
            }
          ]}
          hasPending={hasPending}
          iconHeader={{
            Icon: ServiceIcon,
            iconName: t('Services'),
            onClick: toggleDetailedView
          }}
          iconToggleSubmenu={{
            onClick: toggleDetailedView,
            rotate: toggled,
            testid: 'submenu-service'
          }}
          submenuItems={[
            {
              countTestId: 'submenu services count all',
              onClick: changeFilterAndNavigate({
                criterias: servicesCriterias,
                link: servicesLink,
                toggle: toggleDetailedView
              }),
              submenuCount: numeral(data.total).format('0a'),
              submenuTitle: t('All'),
              titleTestId: 'submenu hosts title all',
              to: servicesLink
            },
            {
              countTestId: 'submenu services count critical',
              onClick: changeFilterAndNavigate({
                criterias: unhandledCriticalServicesCriterias,
                link: unhandledCriticalServicesLink,
                toggle: toggleDetailedView
              }),
              severityCode: SeverityCode.High,
              submenuCount: `${numeral(data.critical.unhandled).format(
                '0a'
              )}/${numeral(data.critical.total).format('0a')}`,
              submenuTitle: t('Critical'),
              titleTestId: 'submenu services title critical',
              to: unhandledCriticalServicesLink
            },
            {
              countTestId: 'submenu services count warning',
              onClick: changeFilterAndNavigate({
                criterias: unhandledWarningServicesCriterias,
                link: unhandledWarningServicesLink,
                toggle: toggleDetailedView
              }),
              severityCode: SeverityCode.Medium,
              submenuCount: `${numeral(data.warning.unhandled).format(
                '0a'
              )}/${numeral(data.warning.total).format('0a')}`,
              submenuTitle: t('Warning'),
              titleTestId: 'submenu services title warning',
              to: unhandledWarningServicesLink
            },
            {
              countTestId: 'submenu services count unknown',
              onClick: changeFilterAndNavigate({
                criterias: unhandledUnknownServicesCriterias,
                link: unhandledUnknownServicesLink,
                toggle: toggleDetailedView
              }),
              severityCode: SeverityCode.Low,
              submenuCount: `${numeral(data.unknown.unhandled).format(
                '0a'
              )}/${numeral(data.unknown.total).format('0a')}`,
              submenuTitle: t('Unknown'),
              titleTestId: 'submenu services title unknown',
              to: unhandledUnknownServicesLink
            },
            {
              countTestId: 'submenu services count ok',
              onClick: changeFilterAndNavigate({
                criterias: okServicesCriterias,
                link: okServicesLink,
                toggle: toggleDetailedView
              }),
              severityCode: SeverityCode.Ok,
              submenuCount: numeral(data.ok).format('0a'),
              submenuTitle: t('Ok'),
              titleTestId: 'submenu services title ok',
              to: okServicesLink
            },
            {
              onClick: changeFilterAndNavigate({
                criterias: pendingServicesCriterias,
                link: pendingServicesLink,
                toggle: toggleDetailedView
              }),
              severityCode: SeverityCode.Pending,
              submenuCount: numeral(data.pending).format('0a'),
              submenuTitle: t('Pending'),
              to: pendingServicesLink
            }
          ]}
          toggled={toggled}
        />
      )}
    </RessourceStatusCounter>
  );
};

export default withTranslation()(ServiceStatusCounter);
