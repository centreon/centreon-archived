import * as yup from 'yup';
import numeral from 'numeral';
import { useNavigate } from 'react-router-dom';
import { useTranslation, withTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import HostIcon from '@mui/icons-material/Dns';

import { SubmenuHeader, SeverityCode, SelectEntry } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { Criteria } from '../../../Resources/Filter/Criterias/models';
import { applyFilterDerivedAtom } from '../../../Resources/Filter/filterAtoms';
import {
  getHostResourcesUrl,
  downCriterias,
  unreachableCriterias,
  upCriterias,
  pendingCriterias,
  unhandledStateCriterias,
  hostCriterias
} from '../getResourcesUrl';
import RessourceStatusCounter from '..';
import getDefaultCriterias from '../../../Resources/Filter/Criterias/default';

const hostStatusEndpoint =
  'internal.php?object=centreon_topcounter&action=hosts_status';

const numberFormat = yup.number().required().integer();

const statusSchema = yup.object().shape({
  down: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat
  }),
  ok: numberFormat,
  pending: numberFormat,
  refreshTime: numberFormat,
  total: numberFormat,
  unreachable: yup.object().shape({
    total: numberFormat,
    unhandled: numberFormat
  })
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

interface SelectResourceProps {
  criterias: Array<Criteria>;
  link: string;
  toggle?: () => void;
}

const HostStatusCounter = (): JSX.Element => {
  const navigate = useNavigate();

  const { t } = useTranslation();

  const { use_deprecated_pages } = useAtomValue(userAtom);
  const applyFilter = useUpdateAtom(applyFilterDerivedAtom);

  const unhandledDownHostsCriterias = getDefaultCriterias({
    resourceTypes: hostCriterias.value,
    states: unhandledStateCriterias.value,
    statuses: downCriterias.value as Array<SelectEntry>
  });
  const unhandledDownHostsLink = use_deprecated_pages
    ? '/main.php?p=20202&o=h_down&search='
    : getHostResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: downCriterias
      });

  const unhandledUnreachableHostsCriterias = getDefaultCriterias({
    resourceTypes: hostCriterias.value,
    states: unhandledStateCriterias.value,
    statuses: unreachableCriterias.value as Array<SelectEntry>
  });
  const unhandledUnreachableHostsLink = use_deprecated_pages
    ? '/main.php?p=20202&o=h_unreachable&search='
    : getHostResourcesUrl({
        stateCriterias: unhandledStateCriterias,
        statusCriterias: unreachableCriterias
      });

  const upHostsCriterias = getDefaultCriterias({
    resourceTypes: hostCriterias.value,
    statuses: upCriterias.value as Array<SelectEntry>
  });
  const upHostsLink = use_deprecated_pages
    ? '/main.php?p=20202&o=h_up&search='
    : getHostResourcesUrl({
        statusCriterias: upCriterias
      });

  const hostsCriterias = getDefaultCriterias({
    resourceTypes: hostCriterias.value
  });
  const hostsLink = use_deprecated_pages
    ? '/main.php?p=20202&o=h&search='
    : getHostResourcesUrl();

  const pendingHostsCriterias = getDefaultCriterias({
    resourceTypes: hostCriterias.value,
    statuses: pendingCriterias.value as Array<SelectEntry>
  });
  const pendingHostsLink = use_deprecated_pages
    ? '/main.php?p=20202&o=h_pending&search='
    : getHostResourcesUrl({
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
    <RessourceStatusCounter<HostData>
      endpoint={hostStatusEndpoint}
      loaderWidth={12}
      schema={statusSchema}
    >
      {({ hasPending, toggled, toggleDetailedView, data }): JSX.Element => (
        <div>
          <SubmenuHeader
            active={toggled}
            counters={[
              {
                count: data.down.unhandled,
                onClick: changeFilterAndNavigate({
                  criterias: unhandledDownHostsCriterias,
                  link: unhandledDownHostsLink
                }),
                severityCode: SeverityCode.High,
                testId: 'Hosts Down',
                to: unhandledDownHostsLink
              },
              {
                count: data.unreachable.unhandled,
                onClick: changeFilterAndNavigate({
                  criterias: unhandledUnreachableHostsCriterias,
                  link: unhandledUnreachableHostsLink
                }),
                severityCode: SeverityCode.Low,
                testId: 'Hosts Unreachable',
                to: unhandledUnreachableHostsLink
              },
              {
                count: data.ok,
                onClick: changeFilterAndNavigate({
                  criterias: upHostsCriterias,
                  link: upHostsLink
                }),
                severityCode: SeverityCode.Ok,
                testId: 'Hosts Up',
                to: upHostsLink
              }
            ]}
            hasPending={hasPending}
            iconHeader={{
              Icon: HostIcon,
              iconName: t('Hosts'),
              onClick: toggleDetailedView
            }}
            iconToggleSubmenu={{
              onClick: toggleDetailedView,
              rotate: toggled,
              testid: 'submenu-hosts'
            }}
            submenuItems={[
              {
                countTestId: 'submenu hosts count all',
                onClick: changeFilterAndNavigate({
                  criterias: hostsCriterias,
                  link: hostsLink,
                  toggle: toggleDetailedView
                }),
                submenuCount: numeral(data.total).format('0a'),
                submenuTitle: t('All'),
                titleTestId: 'submenu hosts title all',
                to: hostsLink
              },
              {
                countTestId: 'submenu hosts count down',
                onClick: changeFilterAndNavigate({
                  criterias: unhandledDownHostsCriterias,
                  link: unhandledDownHostsLink,
                  toggle: toggleDetailedView
                }),
                severityCode: SeverityCode.High,
                submenuCount: `${numeral(data.down.unhandled).format(
                  '0a'
                )}/${numeral(data.down.total).format('0a')}`,
                submenuTitle: t('Down'),
                titleTestId: 'submenu hosts title down',
                to: unhandledDownHostsLink
              },
              {
                countTestId: 'submenu hosts count unreachable',
                onClick: changeFilterAndNavigate({
                  criterias: unhandledUnreachableHostsCriterias,
                  link: unhandledUnreachableHostsLink,
                  toggle: toggleDetailedView
                }),
                severityCode: SeverityCode.Low,
                submenuCount: `${numeral(data.unreachable.unhandled).format(
                  '0a'
                )}/${numeral(data.unreachable.total).format('0a')}`,
                submenuTitle: t('Unreachable'),
                titleTestId: 'submenu hosts title unreachable',
                to: unhandledUnreachableHostsLink
              },
              {
                countTestId: 'submenu hosts count ok',
                onClick: changeFilterAndNavigate({
                  criterias: upHostsCriterias,
                  link: upHostsLink,
                  toggle: toggleDetailedView
                }),
                severityCode: SeverityCode.Ok,
                submenuCount: numeral(data.ok).format('0a'),
                submenuTitle: t('Up'),
                titleTestId: 'submenu hosts title ok',
                to: upHostsLink
              },
              {
                countTestId: 'submenu hosts count pending',
                onClick: changeFilterAndNavigate({
                  criterias: pendingHostsCriterias,
                  link: pendingHostsLink,
                  toggle: toggleDetailedView
                }),
                severityCode: SeverityCode.Pending,
                submenuCount: numeral(data.pending).format('0a'),
                submenuTitle: t('Pending'),
                titleTestId: 'submenu hosts title pending',
                to: pendingHostsLink
              }
            ]}
            toggled={toggled}
          />
        </div>
      )}
    </RessourceStatusCounter>
  );
};

export default withTranslation()(HostStatusCounter);
