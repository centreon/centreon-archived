import * as React from 'react';

import { useSelector } from 'react-redux';
import { isNil, equals, not, prop } from 'ramda';
import useDeepCompareEffect from 'use-deep-compare-effect';

import { SelectEntry } from '@centreon/ui/src';

import { useResourceContext } from '../../Context';
import { SortOrder } from '../../models';

export interface LoadResources {
  initAutorefreshAndLoad: () => void;
}

const useLoadResources = (): LoadResources => {
  const {
    limit,
    page,
    setPage,
    nextSearch,
    setListing,
    sendRequest,
    enabledAutorefresh,
    customFilters,
    loadDetails,
    details,
    selectedResourceId,
    getCriteriaValue,
    filter,
  } = useResourceContext();

  const refreshIntervalRef = React.useRef<number>();

  const refreshIntervalMs = useSelector(
    (state) => state.intervals.AjaxTimeReloadMonitoring * 1000,
  );

  const getSort = (): { [sortField: string]: SortOrder } | undefined => {
    const sort = getCriteriaValue('sort');

    if (isNil(sort)) {
      return undefined;
    }

    const [sortField, sortOrder] = sort as [string, SortOrder];

    return { [sortField]: sortOrder };
  };

  const load = (): void => {
    const searchCriteria = getCriteriaValue('search');
    const search = searchCriteria
      ? {
          regex: {
            value: searchCriteria,
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

    const getCriteriaIds = (name: string) => {
      const criteriaValue = getCriteriaValue(name) as
        | Array<SelectEntry>
        | undefined;

      return criteriaValue?.map(prop('id'));
    };

    sendRequest({
      resourceTypes: getCriteriaIds('resource_types'),
      states: getCriteriaIds('states'),
      statuses: getCriteriaIds('statuses'),
      hostGroupIds: getCriteriaIds('host_groups'),
      serviceGroupIds: getCriteriaIds('service_groups'),
      monitoringServerIds: getCriteriaIds('monitoring_servers'),
      sort: getSort(),
      limit,
      page,
      search,
    }).then(setListing);

    if (isNil(details)) {
      return;
    }

    loadDetails();
  };

  const initAutorefresh = (): void => {
    window.clearInterval(refreshIntervalRef.current);

    const interval = enabledAutorefresh
      ? window.setInterval(() => {
          load();
        }, refreshIntervalMs)
      : undefined;

    refreshIntervalRef.current = interval;
  };

  const initAutorefreshAndLoad = (): void => {
    if (
      isNil(customFilters) ||
      not(equals(getCriteriaValue('search'), nextSearch))
    ) {
      return;
    }

    initAutorefresh();
    load();
  };

  React.useEffect(() => {
    initAutorefresh();
  }, [enabledAutorefresh, selectedResourceId]);

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

  useDeepCompareEffect(() => {
    if (page === 1) {
      initAutorefreshAndLoad();
    }

    setPage(1);
  }, [limit, ...filter.criterias]);

  return { initAutorefreshAndLoad };
};

export default useLoadResources;
