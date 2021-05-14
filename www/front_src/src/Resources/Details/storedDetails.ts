import { getStoredOrDefault, store } from '../storage';

const key = 'centreon-resource-status-details-21.10';

let cachedPanelWidth;

const getStoredOrDefaultPanelWidth = (defaultValue: number): number => {
  return getStoredOrDefault<number>({
    cachedItem: cachedPanelWidth,
    defaultValue,
    key,
    onCachedItemUpdate: (updatedItem) => {
      cachedPanelWidth = updatedItem;
    },
  });
};

const storePanelWidth = (panelWidth: number): void => {
  store({ key, value: panelWidth });
};

const clearCachedPanelWidth = (): void => {
  cachedPanelWidth = null;
};

export {
  getStoredOrDefaultPanelWidth,
  storePanelWidth,
  clearCachedPanelWidth,
  key,
};
