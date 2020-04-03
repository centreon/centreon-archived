import { isNil } from 'ramda';

import { unhandledProblemsFilter, FilterGroup } from './Filter/models';

const key = 'centreon-events-filter';

const defaultFilter = unhandledProblemsFilter;

const getStoredOrDefaultFilter = (): FilterGroup => {
  const foundLocalStorageFilter = localStorage.getItem(key);

  if (isNil(foundLocalStorageFilter)) {
    return defaultFilter;
  }

  return JSON.parse(foundLocalStorageFilter);
};

const storeFilter = (filter): void => {
  localStorage.setItem(key, JSON.stringify(filter));
};

export { getStoredOrDefaultFilter, storeFilter };
