import { getStoredOrDefault, store } from '../../../storage';

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

const storeDetailsCards = (detailsCards: Array<string>): void => {
  cachedDetailsCards = detailsCards;
  store({ key, value: detailsCards });
};

export { getStoredOrDefaultDetailsCards, storeDetailsCards, key };
