/* eslint-disable no-restricted-syntax */
import { createSelector } from 'reselect';

/**
 * find showable in children and groups props
 * @param {Array} acc
 * @param {Object} item
 * @return {Array} accumulator of showable elements
 */
const filterShowableElements = (acc, item) => {
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
        },
      ];
    }
  }

  return [...acc, item];
};

/**
 * remove groups which have no child
 * @param {Array} acc
 * @param {Object} item
 * @return {Array} accumulator of groups which are not empty
 */
const removeEmptyGroups = (acc, item) => {
  if (item.children) {
    return [
      ...acc,
      {
        ...item,
        children: item.children.reduce(removeEmptyGroups, []),
      },
    ];
  }

  if (item.groups) {
    return [
      ...acc,
      {
        ...item,
        groups: item.groups.filter(filterNotEmptyGroup),
      },
    ];
  }

  return [...acc, item];
};

/**
 * check if a group is empty or not
 * @param {Array} group
 * @return {Boolean} if the group is empty or not
 */
const filterNotEmptyGroup = (group) => {
  if (group.children) {
    for (const child of group.children) {
      if (child.show === true) {
        return true;
      }
    }
  }

  return false;
};

const getNavigationItems = (state) => state.navigation.items;

export const menuSelector = createSelector(getNavigationItems, (navItems) => {
  if (navItems === undefined) {
    return [];
  }

  return navItems
    .reduce(filterShowableElements, [])
    .reduce(removeEmptyGroups, []);
});
