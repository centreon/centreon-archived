import * as React from 'react';

import {
  always,
  equals,
  ifElse,
  isNil,
  not,
  pathEq,
  pathOr,
  prop,
} from 'ramda';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { useAtom } from 'jotai';
import { useTranslation } from 'react-i18next';

import { getData, SelectEntry, useRequest } from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import { ResourceListing, SortOrder } from '../../models';
import { searchableFields } from '../../Filter/Criterias/searchQueryLanguage';
import {
  clearSelectedResourceDerivedAtom,
  detailsAtom,
  selectedResourceDetailsEndpointDerivedAtom,
  selectedResourceIdAtom,
  selectedResourceUuidAtom,
  sendingDetailsAtom,
} from '../../Details/detailsAtoms';
import {
  enabledAutorefreshAtom,
  limitAtom,
  listingAtom,
  pageAtom,
  sendingAtom,
} from '../listingAtoms';
import { listResources } from '../api';
import {
  labelNoResourceFound,
  labelSomethingWentWrong,
} from '../../translatedLabels';
import ApiNotFoundMessage from '../ApiNotFoundMessage';
import { ResourceDetails } from '../../Details/models';
import {
  appliedFilterAtom,
  customFiltersAtom,
  getCriteriaValueDerivedAtom,
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
      always(ApiNotFoundMessage),
      pathOr(t(labelSomethingWentWrong), ['response', 'data', 'message']),
    ),
    request: listResources,
  });

  const { sendRequest: sendLoadDetailsRequest, sending: sendingDetails } =
    useRequest<ResourceDetails>({
      getErrorMessage: ifElse(
        pathEq(['response', 'status'], 404),
        always(t(labelNoResourceFound)),
        pathOr(t(labelSomethingWentWrong), ['response', 'data', 'message']),
      ),
      request: getData,
    });

  const { refreshInterval } = useUserContext();

  const [page, setPage] = useAtom(pageAtom);
  const [details, setDetails] = useAtom(detailsAtom);
  const selectedResourceId = useAtomValue(selectedResourceIdAtom);
  const selectedResourceUuid = useAtomValue(selectedResourceUuidAtom);
  const limit = useAtomValue(limitAtom);
  const enabledAutorefresh = useAtomValue(enabledAutorefreshAtom);
  const selectedResourceDetailsEndpoint = useAtomValue(
    selectedResourceDetailsEndpointDerivedAtom,
  );
  const customFilters = useAtomValue(customFiltersAtom);
  const getCriteriaValue = useAtomValue(getCriteriaValueDerivedAtom);
  const appliedFilter = useAtomValue(appliedFilterAtom);
  const setListing = useUpdateAtom(listingAtom);
  const setSending = useUpdateAtom(sendingAtom);
  const setSendingDetails = useUpdateAtom(sendingDetailsAtom);
  const clearSelectedResource = useUpdateAtom(clearSelectedResourceDerivedAtom);

  const refreshIntervalRef = React.useRef<number>();

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
      ...secondSortCriteria,
    };
  };

  const loadDetails = (): void => {
    if (isNil(selectedResourceId)) {
      return;
    }

    sendLoadDetailsRequest(selectedResourceDetailsEndpoint)
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

  React.useEffect(() => {
    setSending(sending);
  }, [sending]);

  React.useEffect(() => {
    setSendingDetails(sending);
  }, [sendingDetails]);

  React.useEffect(() => {
    setDetails(undefined);
    loadDetails();
  }, [selectedResourceUuid]);

  return { initAutorefreshAndLoad };
};

export default useLoadResources;
