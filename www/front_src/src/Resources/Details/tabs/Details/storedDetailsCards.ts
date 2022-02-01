import { getStoredOrDefault } from '../../../storage';

const key = 'centreon-resource-status-details-card-21.10';

let cachedDetailsCards;

const getStoredOrDefaultDetailsCards = (
  defaultValue: Array<string>,
): Array<string> => {
  return getStoredOrDefault<Array<string>>({
    cachedItem: cachedDetailsCards,
    defaultValue,
    key,
    onCachedItemUpdate: (updatedItem) => {
      cachedDetailsCards = updatedItem;
    },
  });
};

export { getStoredOrDefaultDetailsCards, key };