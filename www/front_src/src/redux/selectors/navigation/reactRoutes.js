/* eslint-disable no-param-reassign */
/* eslint-disable no-restricted-syntax */

import { createSelector } from 'reselect';

/**
 * find react routes in children and groups props
 * @param {Array} acc
 * @param {Object} item
 * @return {Array} accumulator of react routes
 */
const findReactRoutes = (acc, item) => {
  for (const parameter of ['groups', 'children']) {
    if (item[parameter]) {
      acc = item[parameter].reduce(findReactRoutes, acc);
    }
  }

  if (item.is_react === true) {
    acc[item.url] = item.page;
  }

  return acc;
};

const getNavigationItems = (state) => state.navigation.items;

export const reactRoutesSelector = createSelector(
  getNavigationItems,
  (navItems) => navItems?.reduce(findReactRoutes, {}),
);
