import { getStoredOrDefault, store } from '../storage';

import { Filter } from './models';

const key = 'centreon-resource-status-filter';

let cachedFilter;

const getStoredOrDefaultFilter = (defaultValue: Filter): Filter => {
  return getStoredOrDefault<Filter>({
    defaultValue,
    key,
    cachedItem: cachedFilter,
    onCachedItemUpdate: (updatedItem) => {
      cachedFilter = updatedItem;
    },
  });
};

const storeFilter = (filter: Filter): void => {
  store<Filter>({ value: filter, key });
};

const clearCachedFilter = (): void => {
  cachedFilter = null;
};

export { getStoredOrDefaultFilter, storeFilter, clearCachedFilter };
