import { isNil } from 'ramda';

const baseKey = 'centreon-resource-status-21.04-';

interface StoredItemParameters<TItem> {
  cachedItem: TItem;
  defaultValue: TItem;
  key: string;
  onCachedItemUpdate: (updatedItem: TItem) => void;
}

const getStoredOrDefault = <TItem>({
  cachedItem,
  defaultValue,
  onCachedItemUpdate,
  key,
}: StoredItemParameters<TItem>): TItem => {
  if (!isNil(cachedItem)) {
    return cachedItem;
  }

  const foundItemInStorage = localStorage.getItem(key);

  if (isNil(foundItemInStorage)) {
    return defaultValue;
  }

  const updatedCachedItem = JSON.parse(foundItemInStorage);

  onCachedItemUpdate(updatedCachedItem);

  return updatedCachedItem;
};

interface StoreParameters<TItem> {
  key: string;
  value: TItem;
}

const store = <TItem>({ value, key }: StoreParameters<TItem>): void => {
  localStorage.setItem(key, JSON.stringify(value));
};

export { getStoredOrDefault, store, baseKey };
