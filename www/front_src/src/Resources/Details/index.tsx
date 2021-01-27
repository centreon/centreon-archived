import * as React from 'react';

import { isNil, isEmpty, pipe, not, defaultTo, propEq, findIndex } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Tab, useTheme, fade } from '@material-ui/core';

import { Panel } from '@centreon/ui';

import { ResourceContext, useResourceContext } from '../Context';
import { rowColorConditions } from '../colors';
import memoizeComponent from '../memoizedComponent';

import Header from './Header';
import { ResourceDetails } from './models';
import { TabById, detailsTabId, tabs } from './tabs';
import { Tab as TabModel, TabId } from './tabs/models';

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

type Props = Pick<
  ResourceContext,
  | 'details'
  | 'openDetailsTabId'
  | 'clearSelectedResource'
  | 'panelWidth'
  | 'setOpenDetailsTabId'
  | 'setPanelWidth'
>;

const DetailsContent = ({
  details,
  openDetailsTabId,
  clearSelectedResource,
  panelWidth,
  setOpenDetailsTabId,
  setPanelWidth,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const theme = useTheme();

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

const memoProps = ['openDetailsTabId', 'details', 'panelWidth'];

const MemoizedDetailsContent = memoizeComponent<Props>({
  memoProps,
  Component: DetailsContent,
});

const Details = (): JSX.Element => {
  const {
    openDetailsTabId,
    details,
    panelWidth,
    setOpenDetailsTabId,
    clearSelectedResource,
    setPanelWidth,
  } = useResourceContext();

  return (
    <MemoizedDetailsContent
      openDetailsTabId={openDetailsTabId}
      details={details}
      panelWidth={panelWidth}
      setOpenDetailsTabId={setOpenDetailsTabId}
      clearSelectedResource={clearSelectedResource}
      setPanelWidth={setPanelWidth}
    />
  );
};

export default Details;
