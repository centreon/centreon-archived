import { isNil } from 'ramda';

interface StoredItemParameters<TItem> {
  cachedItem: TItem;
  defaultValue: TItem;
  onCachedItemUpdate: (updatedItem: TItem) => void;
  key: string;
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
  value: TItem;
  key: string;
}

const store = <TItem>({ value, key }: StoreParameters<TItem>): void => {
  localStorage.setItem(key, JSON.stringify(value));
};

export { getStoredOrDefault, store };
