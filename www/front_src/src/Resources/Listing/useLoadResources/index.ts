import * as React from 'react';

import { useSelector } from 'react-redux';
import { isNil, prop } from 'ramda';

import { SelectEntry } from '@centreon/ui';

import { useResourceContext } from '../../Context';
import { SortOrder } from '../../models';
import { searchableFields } from '../../Filter/Criterias/searchQueryLanguage';

export interface LoadResources {
  initAutorefreshAndLoad: () => void;
}

const useLoadResources = (): LoadResources => {
  const {
    limit,
    page,
    setPage,
    setListing,
    sendRequest,
    enabledAutorefresh,
    customFilters,
    loadDetails,
    details,
    selectedResourceId,
    getCriteriaValue,
    appliedFilter,
  } = useResourceContext();

  const refreshIntervalRef = React.useRef<number>();

  const refreshIntervalMs = useSelector(
    (state: { intervals }) => state.intervals.AjaxTimeReloadMonitoring * 1000,
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
            fields: searchableFields,
            value: searchCriteria,
          },
        }
      : undefined;

    const getCriteriaIds = (
      name: string,
    ): Array<string | number> | undefined => {
      const criteriaValue = getCriteriaValue(name) as
        | Array<SelectEntry>
        | undefined;

      return criteriaValue?.map(prop('id'));
    };

    const getCriteriaNames = (name: string): Array<string> => {
      const criteriaValue = getCriteriaValue(name) as
        | Array<SelectEntry>
        | undefined;

      return criteriaValue?.map(prop('name')) as Array<string>;
    };

    sendRequest({
      hostGroups: getCriteriaNames('host_groups'),
      limit,
      monitoringServers: getCriteriaNames('monitoring_servers'),
      page,
      resourceTypes: getCriteriaIds('resource_types'),
      search,
      serviceGroups: getCriteriaNames('service_groups'),
      sort: getSort(),
      states: getCriteriaIds('states'),
      statuses: getCriteriaIds('statuses'),
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
    if (isNil(customFilters)) {
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
    if (isNil(details)) {
      return;
    }

    initAutorefresh();
  }, [isNil(details)]);

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
  }, [limit, appliedFilter]);

  return { initAutorefreshAndLoad };
};

export default useLoadResources;
