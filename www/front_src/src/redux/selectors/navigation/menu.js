import { createSelector } from 'reselect';

function filterShowableElements(acc, item) {
  if (item.show === false) {
    return acc;
  }

  for (const parameter of ['groups', 'children']) {
    if (item[parameter]) {
      return [
        ...acc,
        {
          ...item,
          [parameter]: item[parameter].reduce(filterShowableElements, []),
        }
      ];
    }
  }

  return [
    ...acc,
    item
  ];
}

const getNavigationItems = (state) => state.navigation.items;

export const menuSelector = createSelector(
  getNavigationItems,
  (navItems) => navItems.reduce(filterShowableElements, []),
);