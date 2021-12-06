/* eslint-disable no-param-reassign */
/* eslint-disable no-restricted-syntax */
import { createSelector } from 'reselect';

/**
 * get allowed pages in children and groups props
 * @param {Array} acc
 * @param {Object} item
 * @return {Array} accumulator of allowed pages
 */
const getAllowedPages = (acc, item) => {
  for (const parameter of ['groups', 'children']) {
    if (item[parameter]) {
      acc = item[parameter].reduce(getAllowedPages, acc);
    }
  }

  if (item.is_react === true) {
    acc.push(item.url);
  } else if (item.page) {
    acc.push(item.page);
  }

  return acc;
};

const getNavigationItems = (state) => state.navigation.items;

export const allowedPagesSelector = createSelector(
  getNavigationItems,
  (items) => (items ? items.reduce(getAllowedPages, []) : undefined),
);
