import { isNil } from 'ramda';

const getStoredOrDefault = <TItem>({
  cachedItem,
  defaultValue,
  onCachedItemUpdate,
  key,
}): TItem => {
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

const store = <TItem>({ value, key }): void => {
  localStorage.setItem(key, JSON.stringify(value));
};

export { getStoredOrDefault, store };
