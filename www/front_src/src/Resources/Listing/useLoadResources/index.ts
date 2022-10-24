import { useRef, useEffect } from 'react';

import {
  always,
  equals,
  ifElse,
  isNil,
  not,
  pathEq,
  pathOr,
  prop
} from 'ramda';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { useAtom } from 'jotai';
import { useTranslation } from 'react-i18next';

import {
  getData,
  SelectEntry,
  useRequest,
  getUrlQueryParameters
} from '@centreon/ui';
import { refreshIntervalAtom } from '@centreon/ui-context';

import { ResourceListing, SortOrder } from '../../models';
import { searchableFields } from '../../Filter/Criterias/searchQueryLanguage';
import {
  clearSelectedResourceDerivedAtom,
  detailsAtom,
  selectedResourceDetailsEndpointDerivedAtom,
  selectedResourceUuidAtom,
  sendingDetailsAtom,
  selectedResourcesDetailsAtom
} from '../../Details/detailsAtoms';
import {
  enabledAutorefreshAtom,
  limitAtom,
  listingAtom,
  pageAtom,
  sendingAtom
} from '../listingAtoms';
import { listResources } from '../api';
import {
  labelNoResourceFound,
  labelSomethingWentWrong
} from '../../translatedLabels';
import { ResourceDetails } from '../../Details/models';
import {
  appliedFilterAtom,
  customFiltersAtom,
  getCriteriaValueDerivedAtom
} from '../../Filter/filterAtoms';

export interface LoadResources {
  initAutorefreshAndLoad: () => void;
}

const secondSortField = 'last_status_change';
const defaultSecondSortCriteria = { [secondSortField]: SortOrder.desc };

const useLoadResources = (): LoadResources => {
  const { t } = useTranslation();

  const { sendRequest, sending } = useRequest<ResourceListing>({
    getErrorMessage: ifElse(
      pathEq(['response', 'status'], 404),
      always(t(labelNoResourceFound)),
      pathOr(t(labelSomethingWentWrong), ['response', 'data', 'message'])
    ),
    request: listResources
  });

  const { sendRequest: sendLoadDetailsRequest, sending: sendingDetails } =
    useRequest<ResourceDetails>({
      getErrorMessage: ifElse(
        pathEq(['response', 'status'], 404),
        always(t(labelNoResourceFound)),
        pathOr(t(labelSomethingWentWrong), ['response', 'data', 'message'])
      ),
      request: getData
    });

  const [page, setPage] = useAtom(pageAtom);
  const [details, setDetails] = useAtom(detailsAtom);
  const refreshInterval = useAtomValue(refreshIntervalAtom);
  const selectedResourceUuid = useAtomValue(selectedResourceUuidAtom);
  const limit = useAtomValue(limitAtom);
  const enabledAutorefresh = useAtomValue(enabledAutorefreshAtom);
  const selectedResourceDetailsEndpoint = useAtomValue(
    selectedResourceDetailsEndpointDerivedAtom
  );
  const selectedResourceDetails = useAtomValue(selectedResourcesDetailsAtom);
  const customFilters = useAtomValue(customFiltersAtom);
  const getCriteriaValue = useAtomValue(getCriteriaValueDerivedAtom);
  const appliedFilter = useAtomValue(appliedFilterAtom);
  const setListing = useUpdateAtom(listingAtom);
  const setSending = useUpdateAtom(sendingAtom);
  const setSendingDetails = useUpdateAtom(sendingDetailsAtom);
  const clearSelectedResource = useUpdateAtom(clearSelectedResourceDerivedAtom);
  const refreshIntervalRef = useRef<number>();

  const refreshIntervalMs = refreshInterval * 1000;

  const getSort = (): { [sortField: string]: SortOrder } | undefined => {
    const sort = getCriteriaValue('sort');

    if (isNil(sort)) {
      return undefined;
    }

    const [sortField, sortOrder] = sort as [string, SortOrder];

    const secondSortCriteria =
      not(equals(sortField, secondSortField)) && defaultSecondSortCriteria;

    return {
      [sortField]: sortOrder,
      ...secondSortCriteria
    };
  };

  const loadDetails = (): void => {
    if (isNil(selectedResourceDetails?.resourceId)) {
      return;
    }

    sendLoadDetailsRequest({
      endpoint: selectedResourceDetailsEndpoint
    })
      .then(setDetails)
      .catch(() => {
        clearSelectedResource();
      });
  };

  const load = (): void => {
    const searchCriteria = getCriteriaValue('search');
    const search = searchCriteria
      ? {
          regex: {
            fields: searchableFields,
            value: searchCriteria
          }
        }
      : undefined;

    const getCriteriaIds = (
      name: string
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

    const getCriteriaLevels = (name: string): Array<number> => {
      const criteriaValue = getCriteriaValue(name) as
        | Array<SelectEntry>
        | undefined;

      const results = criteriaValue?.map(prop('name'));

      return results?.map((item) => Number(item)) as Array<number>;
    };

    if (getUrlQueryParameters().fromTopCounter) {
      return;
    }

    sendRequest({
      hostCategories: getCriteriaNames('host_categories'),
      hostGroups: getCriteriaNames('host_groups'),
      hostSeverities: getCriteriaNames('host_severities'),
      hostSeverityLevels: getCriteriaLevels('host_severity_levels'),
      limit,
      monitoringServers: getCriteriaNames('monitoring_servers'),
      page,
      resourceTypes: getCriteriaIds('resource_types'),
      search,
      serviceCategories: getCriteriaNames('service_categories'),
      serviceGroups: getCriteriaNames('service_groups'),
      serviceSeverities: getCriteriaNames('service_severities'),
      serviceSeverityLevels: getCriteriaLevels('service_severity_levels'),
      sort: getSort(),
      states: getCriteriaIds('states'),
      statusTypes: getCriteriaIds('status_types'),
      statuses: getCriteriaIds('statuses')
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

  useEffect(() => {
    initAutorefresh();
  }, [enabledAutorefresh, selectedResourceDetails?.resourceId]);

  useEffect(() => {
    return (): void => {
      clearInterval(refreshIntervalRef.current);
    };
  }, []);

  useEffect(() => {
    if (isNil(details)) {
      return;
    }

    initAutorefresh();
  }, [isNil(details)]);

  useEffect(() => {
    if (isNil(page)) {
      return;
    }

    initAutorefreshAndLoad();
  }, [page]);

  useEffect(() => {
    if (page === 1) {
      initAutorefreshAndLoad();
    }

    setPage(1);
  }, [limit, appliedFilter]);

  useEffect(() => {
    setSending(sending);
  }, [sending]);

  useEffect(() => {
    setSendingDetails(sending);
  }, [sendingDetails]);

  useEffect(() => {
    setDetails(undefined);
    loadDetails();
  }, [
    selectedResourceUuid,
    selectedResourceDetails?.parentResourceId,
    selectedResourceDetails?.resourceId
  ]);

  return { initAutorefreshAndLoad };
};

export default useLoadResources;
