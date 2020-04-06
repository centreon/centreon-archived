import * as React from 'react';

import {
  getStoredOrDefaultFilter,
  clearCachedFilter,
  storeFilter,
} from '../storedFilter';
import { Filter, FilterGroup, Criterias } from './models';

const getDefaultFilter = (): FilterGroup => getStoredOrDefaultFilter();
const getDefaultCriterias = (): Criterias => getDefaultFilter().criterias;
const getDefaultSearch = (): string | undefined => getDefaultFilter().search;
const getDefaultResourceTypes = (): Array<Filter> =>
  getDefaultCriterias().resourceTypes;
const getDefaultStates = (): Array<Filter> => getDefaultCriterias().states;
const getDefaultStatuses = (): Array<Filter> => getDefaultCriterias().statuses;
const getDefaultHostGroups = (): Array<Filter> =>
  getDefaultCriterias().hostGroups;
const getDefaultServiceGroups = (): Array<Filter> =>
  getDefaultCriterias().serviceGroups;

const useFilter = () => {
  const [filter, setFilter] = React.useState(getStoredOrDefaultFilter());
  const [currentSearch, setCurrentSearch] = React.useState<string | undefined>(
    getDefaultSearch(),
  );
  const [nextSearch, setNextSearch] = React.useState<string | undefined>(
    getDefaultSearch(),
  );
  const [resourceTypes, setResourceTypes] = React.useState<Array<Filter>>(
    getDefaultResourceTypes(),
  );
  const [states, setStates] = React.useState<Array<Filter>>(getDefaultStates());
  const [statuses, setStatuses] = React.useState<Array<Filter>>(
    getDefaultStatuses(),
  );
  const [hostGroups, setHostGroups] = React.useState<Array<Filter>>(
    getDefaultHostGroups(),
  );
  const [serviceGroups, setServiceGroups] = React.useState<Array<Filter>>(
    getDefaultServiceGroups(),
  );

  React.useEffect(() => {
    storeFilter({
      ...filter,
      search: nextSearch,
      criterias: {
        resourceTypes,
        states,
        statuses,
        hostGroups,
        serviceGroups,
      },
    });
  }, [
    filter,
    nextSearch,
    resourceTypes,
    states,
    statuses,
    hostGroups,
    serviceGroups,
  ]);

  React.useEffect(() => (): void => {
    clearCachedFilter();
  });

  return {
    filter,
    setFilter,
    currentSearch,
    setCurrentSearch,
    nextSearch,
    setNextSearch,
    resourceTypes,
    setResourceTypes,
    states,
    setStates,
    statuses,
    setStatuses,
    hostGroups,
    setHostGroups,
    serviceGroups,
    setServiceGroups,
  };
};

export default useFilter;
