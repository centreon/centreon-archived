import { isNil } from 'ramda';

import { Filter } from './models';

const key = 'centreon-resource-status-filter';

let cachedFilter;

const getStoredOrDefaultFilter = (defaultFilter: Filter): Filter => {
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

const storeFilter = (filter: Filter): void => {
  localStorage.setItem(key, JSON.stringify(filter));
};

const clearCachedFilter = (): void => {
  cachedFilter = null;
};

export { getStoredOrDefaultFilter, storeFilter, clearCachedFilter };
