import { getStoredOrDefault, store } from '../storage';

const key = 'centreon-resource-status-details-21.04';

let cachedPanelWidth;

const getStoredOrDefaultPanelWidth = (defaultValue: number): number => {
  return getStoredOrDefault<number>({
    defaultValue,
    key,
    cachedItem: cachedPanelWidth,
    onCachedItemUpdate: (updatedItem) => {
      cachedPanelWidth = updatedItem;
    },
  });
};

const storePanelWidth = (panelWidth: number): void => {
  store({ value: panelWidth, key });
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
