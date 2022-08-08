import { baseKey, getStoredOrDefault } from '../storage';

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

export { getStoredOrDefaultFilter };
