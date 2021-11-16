import { baseKey, getStoredOrDefault, store } from '../storage';

const limitKey = `${baseKey}limit`;

let cachedLimit;

const getStoredOrDefaultLimit = (defaultValue: number): number => {
  return getStoredOrDefault<number>({
    cachedItem: cachedLimit,
    defaultValue,
    key: limitKey,
    onCachedItemUpdate: (updatedItem) => {
      cachedLimit = updatedItem;
    },
  });
};

const storeLimit = (filter: number): void => {
  store<number>({ key: limitKey, value: filter });
};

const clearCachedLimit = (): void => {
  cachedLimit = null;
};

export { getStoredOrDefaultLimit, storeLimit, clearCachedLimit, limitKey };
