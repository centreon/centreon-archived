import * as React from 'react';

import { useSelector } from 'react-redux';

import { isNil, equals, not } from 'ramda';
import { useResourceContext } from '../../Context';

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
    setPage,
    currentSearch,
    nextSearch,
    setListing,
    sendRequest,
    enabledAutorefresh,
    customFilters,
    loadDetails,
  } = useResourceContext();

  const refreshIntervalRef = React.useRef<number>();

  const refreshIntervalMs = useSelector(
    (state) => state.intervals.AjaxTimeReloadMonitoring * 1000,
  );

  const load = (): void => {
    const sort = sortf ? { [sortf]: sorto } : undefined;
    const search = currentSearch
      ? {
          regex: {
            value: currentSearch,
            fields: [
              'h.name',
              'h.alias',
              'h.address',
              's.description',
              'information',
            ],
          },
        }
      : undefined;

    sendRequest({
      states: states.map(({ id }) => id),
      statuses: statuses.map(({ id }) => id),
      resourceTypes: resourceTypes.map(({ id }) => id),
      hostGroupIds: hostGroups?.map(({ id }) => id),
      serviceGroupIds: serviceGroups?.map(({ id }) => id),
      sort,
      limit,
      page,
      search,
    }).then(setListing);

    loadDetails();
  };

  const initAutorefresh = (): void => {
    window.clearInterval(refreshIntervalRef.current);

    const interval = enabledAutorefresh
      ? window.setInterval(load, refreshIntervalMs)
      : undefined;

    refreshIntervalRef.current = interval;
  };

  const initAutorefreshAndLoad = (): void => {
    if (isNil(customFilters) || not(equals(currentSearch, nextSearch))) {
      return;
    }

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
    if (isNil(page)) {
      return;
    }

    initAutorefreshAndLoad();
  }, [page]);

  React.useEffect(() => {
    if (page === 1) {
      initAutorefreshAndLoad();
    }

    setPage(1);
  }, [
    sortf,
    sorto,
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
