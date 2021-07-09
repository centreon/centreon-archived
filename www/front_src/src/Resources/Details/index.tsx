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

import { useTheme, fade } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { MemoizedPanel as Panel, Tab } from '@centreon/ui';

import { useResourceContext } from '../Context';
import { rowColorConditions } from '../colors';
import useTimePeriod, {
  TimePeriodContext,
} from '../Graph/Performance/TimePeriods/useTimePeriod';

import Header from './Header';
import { ResourceDetails } from './models';
import { TabById, detailsTabId, tabs } from './tabs';
import { Tab as TabModel, TabId } from './tabs/models';

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

export interface TabBounds {
  bottom: number;
  top: number;
}

const Context = React.createContext<TabBounds>({
  bottom: 0,
  top: 0,
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
    selectResource,
  } = useResourceContext();

  const timePeriodProps = useTimePeriod({
    details,
  });

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
          acknowledged: !isNil(acknowledgement),
          in_downtime: pipe(defaultTo([]), isEmpty, not)(downtimes),
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
        panelRef.current?.getBoundingClientRect() || { bottom: 0, top: 0 },
      )}
    >
      <TimePeriodContext.Provider value={timePeriodProps}>
        <Panel
          header={<Header details={details} onSelectParent={selectResource} />}
          headerBackgroundColor={getHeaderBackgroundColor()}
          memoProps={[openDetailsTabId, details, panelWidth]}
          ref={panelRef as React.RefObject<HTMLDivElement>}
          selectedTab={<TabById details={details} id={openDetailsTabId} />}
          selectedTabId={getTabIndex(openDetailsTabId)}
          tabs={getVisibleTabs().map(({ id, title }) => (
            <Tab
              disabled={isNil(details)}
              key={id}
              label={isNil(details) ? <Skeleton width={60} /> : t(title)}
              onClick={changeSelectedTabId(id)}
            />
          ))}
          width={panelWidth}
          onClose={clearSelectedResource}
          onResize={setPanelWidth}
        />
      </TimePeriodContext.Provider>
    </Context.Provider>
  );
};

export default Details;
export { Context as TabContext };
