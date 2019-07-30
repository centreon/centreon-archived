import { createSelector } from 'reselect';

function getAllowedPages(acc, item) {
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
}

const getNavigationItems = (state) => state.navigation.items;

export const allowedPagesSelector = createSelector(
  getNavigationItems,
  (items) => items.reduce(getAllowedPages, []),
);