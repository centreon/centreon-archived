import * as React from 'react';

import { useSelector } from 'react-redux';

import { useResourceContext } from '../Context';

export interface LoadResources {
  initAutorefreshAndLoad: () => void;
}

const useLoadResources = (): LoadResources => {
  const {
    sortf,
    sorto,
    states,
    statuses,
    resourceTypes,
    hostGroups,
    serviceGroups,
    limit,
    page,
    currentSearch,
    nextSearch,
    setListing,
    sendRequest,
    enabledAutorefresh,
  } = useResourceContext();

  const refreshIntervalRef = React.useRef<number>();

  const refreshIntervalMs = useSelector(
    (state) => state.intervals.AjaxTimeReloadMonitoring * 1000,
  );

  const load = (): void => {
    const sort = sortf ? { [sortf]: sorto } : undefined;

    sendRequest({
      states: states.map(({ id }) => id),
      statuses: statuses.map(({ id }) => id),
      resourceTypes: resourceTypes.map(({ id }) => id),
      hostGroupIds: hostGroups?.map(({ id }) => id),
      serviceGroupIds: serviceGroups?.map(({ id }) => id),
      sort,
      limit,
      page,
      search: currentSearch,
    }).then(setListing);
  };

  const initAutorefresh = (): void => {
    window.clearInterval(refreshIntervalRef.current);

    const interval = enabledAutorefresh
      ? window.setInterval(load, refreshIntervalMs)
      : undefined;

    refreshIntervalRef.current = interval;
  };

  const initAutorefreshAndLoad = (): void => {
    initAutorefresh();
    load();
  };

  React.useEffect(() => {
    initAutorefresh();
  }, [enabledAutorefresh]);

  React.useEffect(() => {
    return (): void => {
      clearInterval(refreshIntervalRef.current);
    };
  }, []);

  React.useEffect(() => {
    if (currentSearch !== nextSearch) {
      return;
    }
    initAutorefreshAndLoad();
  }, [
    sortf,
    sorto,
    page,
    limit,
    currentSearch,
    states,
    statuses,
    resourceTypes,
    hostGroups,
    serviceGroups,
  ]);

  return { initAutorefreshAndLoad };
};

export default useLoadResources;
