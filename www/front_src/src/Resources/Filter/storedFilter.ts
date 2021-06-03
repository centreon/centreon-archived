import { baseKey, getStoredOrDefault, store } from '../storage';

import { Filter } from './models';

const filterKey = `${baseKey}filter`;
const filterExpandedKey = `${baseKey}filter-expanded`;

let cachedFilter;
let cachedFilterExpanded;

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

const getStoredOrDefaultFilterExpanded = (defaultValue: boolean): boolean => {
  return getStoredOrDefault<boolean>({
    cachedItem: cachedFilterExpanded,
    defaultValue,
    key: filterExpandedKey,
    onCachedItemUpdate: (updatedItem) => {
      cachedFilterExpanded = updatedItem;
    },
  });
};

const storeFilterExpanded = (filterExpanded: boolean): void => {
  store<boolean>({ key: filterExpandedKey, value: filterExpanded });
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
