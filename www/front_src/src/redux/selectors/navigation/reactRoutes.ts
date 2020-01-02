import { createSelector } from 'reselect';

/**
 * find react routes in children and groups props
 * @param {Array} acc
 * @param {Object} item
 * @return {Array} accumulator of react routes
 */
function findReactRoutes(accParam: Array, item: object): Array {
  let acc = accParam;
  ['groups', 'children'].forEach((parameter) => {
    if (item[parameter]) {
      acc = item[parameter].reduce(findReactRoutes, acc);
    }
  });

  if (item.is_react === true) {
    acc[item.url] = item.page;
  }

  return acc;
}

const getNavigationItems = (state: object): Array => state.navigation.items;

const reactRoutesSelector = createSelector(
  getNavigationItems,
  (navItems: Array): Array => navItems.reduce(findReactRoutes, {}),
);

export default reactRoutesSelector;
