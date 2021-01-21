import * as React from 'react';

import { isNil, isEmpty, pipe, not, defaultTo, propEq, findIndex } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Tab, useTheme, fade } from '@material-ui/core';

import { Panel } from '@centreon/ui';

import { useResourceContext } from '../Context';
import { rowColorConditions } from '../colors';

import Header from './Header';
import { ResourceDetails } from './models';
import { TabById, detailsTabId, tabs } from './tabs';
import { Tab as TabModel, TabId } from './tabs/models';

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

const Details = (): JSX.Element | null => {
  const { t } = useTranslation();
  const theme = useTheme();

  const {
    openDetailsTabId,
    setOpenDetailsTabId,
    clearSelectedResource,
    details,
    panelWidth,
    setPanelWidth,
  } = useResourceContext();

  React.useEffect(() => {
    if (isNil(details)) {
      return;
    }

    const isOpenTabActive = tabs
      .find(propEq('id', openDetailsTabId))
      ?.getIsActive(details);

    if (!isOpenTabActive) {
      setOpenDetailsTabId(detailsTabId);
    }
  }, [details]);

  const getVisibleTabs = (): Array<TabModel> => {
    if (isNil(details)) {
      return tabs;
    }

    return tabs.filter(({ getIsActive }) => getIsActive(details));
  };

  const getTabIndex = (tabId: TabId): number => {
    const index = findIndex(propEq('id', tabId), getVisibleTabs());

    return index > 0 ? index : 0;
  };

  const changeSelectedTabId = (tabId: TabId) => (): void => {
    setOpenDetailsTabId(tabId);
  };

  const getHeaderBackgroundColor = (): string | undefined => {
    const { downtimes, acknowledgement } = details || {};

    const foundColorCondition = rowColorConditions(theme).find(
      ({ condition }) =>
        condition({
          in_downtime: pipe(defaultTo([]), isEmpty, not)(downtimes),
          acknowledged: !isNil(acknowledgement),
        }),
    );

    if (isNil(foundColorCondition)) {
      return theme.palette.common.white;
    }

    return fade(foundColorCondition.color, 0.8);
  };

  return (
    <Panel
      onClose={clearSelectedResource}
      header={<Header details={details} />}
      headerBackgroundColor={getHeaderBackgroundColor()}
      tabs={getVisibleTabs().map(({ id, title }) => (
        <Tab
          style={{ minWidth: 'unset' }}
          key={id}
          label={t(title)}
          disabled={isNil(details)}
          onClick={changeSelectedTabId(id)}
        />
      ))}
      selectedTabId={getTabIndex(openDetailsTabId)}
      selectedTab={<TabById id={openDetailsTabId} details={details} />}
      width={panelWidth}
      onResize={setPanelWidth}
    />
  );
};

export default Details;
