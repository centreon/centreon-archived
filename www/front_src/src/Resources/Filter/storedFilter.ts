import { isNil } from 'ramda';

import { unhandledProblemsFilter, Filter } from './models';

const key = 'centreon-events-filter';

const defaultFilter = unhandledProblemsFilter;

let cachedFilter;

const getStoredOrDefaultFilter = (): Filter => {
  if (!isNil(cachedFilter)) {
    return cachedFilter;
  }

  const foundFilterInStorage = localStorage.getItem(key);

  if (isNil(foundFilterInStorage)) {
    return defaultFilter;
  }

  cachedFilter = JSON.parse(foundFilterInStorage);

  return cachedFilter;
};

const storeFilter = (filter): void => {
  localStorage.setItem(key, JSON.stringify(filter));
};

const clearCachedFilter = (): void => {
  cachedFilter = null;
};

export { getStoredOrDefaultFilter, storeFilter, clearCachedFilter };
