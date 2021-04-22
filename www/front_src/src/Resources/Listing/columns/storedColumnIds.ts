import { baseKey, getStoredOrDefault, store } from '../../storage';

const columnIdsKey = `${baseKey}column-ids`;

let cachedColumnIds;

const getStoredOrDefaultColumnIds = (
  defaultValue: Array<string>,
): Array<string> => {
  return getStoredOrDefault<Array<string>>({
    cachedItem: cachedColumnIds,
    defaultValue,
    key: columnIdsKey,
    onCachedItemUpdate: (updatedItem) => {
      cachedColumnIds = updatedItem;
    },
  });
};

const storeColumnIds = (columnIds: Array<string>): void => {
  store<Array<string>>({ key: columnIdsKey, value: columnIds });
};

const clearCachedColumnIds = (): void => {
  cachedColumnIds = null;
};

export {
  getStoredOrDefaultColumnIds,
  storeColumnIds,
  clearCachedColumnIds,
  columnIdsKey,
};
