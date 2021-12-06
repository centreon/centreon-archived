import { baseKey, getStoredOrDefault, store } from '../storage';

import { Filter } from './models';

const filterKey = `${baseKey}filter`;

let cachedFilter;

const getStoredOrDefaultFilter = (defaultValue: Filter): Filter => {
  return getStoredOrDefault<Filter>({
    cachedItem: cachedFilter,
    defaultValue,
    key: filterKey,
    onCachedItemUpdate: (updatedItem) => {
      cachedFilter = updatedItem;
    },
  });
};

const storeFilter = (filter: Filter): void => {
  store<Filter>({ key: filterKey, value: filter });
};

const clearCachedFilter = (): void => {
  cachedFilter = null;
};

export { getStoredOrDefaultFilter, storeFilter, clearCachedFilter, filterKey };
