import * as React from 'react';

import {
  isNil,
  isEmpty,
  pipe,
  not,
  defaultTo,
  propEq,
  findIndex,
  pick,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import { Tab, useTheme, fade } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { MemoizedPanel as Panel } from '@centreon/ui';

import { useResourceContext } from '../Context';
import { rowColorConditions } from '../colors';

import Header from './Header';
import { ResourceDetails } from './models';
import { TabById, detailsTabId, tabs } from './tabs';
import { Tab as TabModel, TabId } from './tabs/models';

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

export interface TabBounds {
  top: number;
  bottom: number;
}

const Context = React.createContext<TabBounds>({
  top: 0,
  bottom: 0,
});

const Details = (): JSX.Element | null => {
  const { t } = useTranslation();
  const theme = useTheme();

  const panelRef = React.useRef<HTMLDivElement>();

  const {
    openDetailsTabId,
    details,
    panelWidth,
    setOpenDetailsTabId,
    clearSelectedResource,
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
    <Context.Provider
      value={pick(
        ['top', 'bottom'],
        panelRef.current?.getBoundingClientRect() || { top: 0, bottom: 0 },
      )}
    >
      <Panel
        ref={panelRef as React.RefObject<HTMLDivElement>}
        onClose={clearSelectedResource}
        header={<Header details={details} />}
        headerBackgroundColor={getHeaderBackgroundColor()}
        tabs={getVisibleTabs().map(({ id, title }) => (
          <Tab
            style={{ minWidth: 'unset' }}
            key={id}
            label={isNil(details) ? <Skeleton width={60} /> : t(title)}
            disabled={isNil(details)}
            onClick={changeSelectedTabId(id)}
          />
        ))}
        selectedTabId={getTabIndex(openDetailsTabId)}
        selectedTab={<TabById id={openDetailsTabId} details={details} />}
        width={panelWidth}
        onResize={setPanelWidth}
        memoProps={[openDetailsTabId, details, panelWidth]}
      />
    </Context.Provider>
  );
};

export default Details;
export { Context as TabContext };
