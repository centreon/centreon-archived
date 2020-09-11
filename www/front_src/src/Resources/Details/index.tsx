import * as React from 'react';

import { isNil, isEmpty, pipe, not, defaultTo, propEq, findIndex } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Panel } from '@centreon/ui';

import { Tab, useTheme, fade } from '@material-ui/core';

import Header from './Header';
import { ResourceDetails } from './models';
import { useResourceContext } from '../Context';
import { TabById, TabId, detailsTabId, tabs } from './tabs';
import { rowColorConditions } from '../colors';

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

  const getTabIndex = (tabId: TabId): number => {
    return findIndex(propEq('id', tabId), tabs);
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
      tabs={tabs.map(({ id, title, getIsActive }) => (
        <Tab
          style={{ minWidth: 'unset' }}
          key={id}
          label={t(title)}
          disabled={isNil(details) || !getIsActive(details)}
          onClick={changeSelectedTabId(id)}
        />
      ))}
      selectedTabId={getTabIndex(openDetailsTabId)}
      selectedTab={<TabById id={openDetailsTabId} details={details} />}
    />
  );
};

export default Details;
