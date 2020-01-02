import { createSelector } from 'reselect';

/**
 * get allowed pages in children and groups props
 * @param {Array} acc
 * @param {Object} item
 * @return {Array} accumulator of allowed pages
 */
function getAllowedPages(accParam: Array, item: object): Array {
  let acc = accParam;

  ['groups', 'children'].forEach((parameter) => {
    if (item[parameter]) {
      acc = item[parameter].reduce(getAllowedPages, acc);
    }
  });

  if (item.is_react === true) {
    acc.push(item.url);
  } else if (item.page) {
    acc.push(item.page);
  }

  return acc;
}

const getNavigationItems = (state: object): Array => state.navigation.items;

const allowedPagesSelector = createSelector(
  getNavigationItems,
  (items: Array): Array => items.reduce(getAllowedPages, []),
);

export default allowedPagesSelector;
