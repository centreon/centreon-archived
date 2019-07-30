import { createSelector } from 'reselect';

function findReactRoutes(acc, item) {
  for (const parameter of ['groups', 'children']) {
    if (item[parameter]) {
      acc = item[parameter].reduce(findReactRoutes, acc);
    }
  }

  if (item.is_react === true) {
    acc[item.url] = item.page;
  }

  return acc;
}

const getNavigationItems = (state) => state.navigation.items;

export const reactRoutesSelector = createSelector(
  getNavigationItems,
  (navItems) => navItems.reduce(findReactRoutes, {}),
);