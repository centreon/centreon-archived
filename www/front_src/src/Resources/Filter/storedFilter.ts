import { baseKey, getStoredOrDefault, store } from '../storage';

import { Filter } from './models';

const filterKey = `${baseKey}filter`;
const filterExpandedKey = `${baseKey}filter-expanded`;

let cachedFilter;
let cachedFilterExpanded;

const getStoredOrDefaultFilter = (defaultValue: Filter): Filter => {
  return getStoredOrDefault<Filter>({
    defaultValue,
    key: filterKey,
    cachedItem: cachedFilter,
    onCachedItemUpdate: (updatedItem) => {
      cachedFilter = updatedItem;
    },
  });
};

const storeFilter = (filter: Filter): void => {
  store<Filter>({ value: filter, key: filterKey });
};

const clearCachedFilter = (): void => {
  cachedFilter = null;
};

const getStoredOrDefaultFilterExpanded = (defaultValue: boolean): boolean => {
  return getStoredOrDefault<boolean>({
    defaultValue,
    key: filterExpandedKey,
    cachedItem: cachedFilterExpanded,
    onCachedItemUpdate: (updatedItem) => {
      cachedFilterExpanded = updatedItem;
    },
  });
};

const storeFilterExpanded = (filterExpanded: boolean): void => {
  store<boolean>({ value: filterExpanded, key: filterExpandedKey });
};

const clearCachedFilterExpanded = (): void => {
  cachedFilterExpanded = null;
};

export {
  getStoredOrDefaultFilter,
  storeFilter,
  clearCachedFilter,
  filterKey,
  filterExpandedKey,
  getStoredOrDefaultFilterExpanded,
  storeFilterExpanded,
  clearCachedFilterExpanded,
};
