import { createSelector } from 'reselect';

/**
 * find showable in children and groups props
 * @param {Array} acc
 * @param {Object} item
 * @return {Array} accumulator of showable elements
 */
function filterShowableElements(acc: Array, item: object): Array {
  if (item.show === false) {
    return acc;
  }

  const parameters = ['groups', 'children'];

  for (let i = 0; i < parameters.length; i += 1) {
    const parameter = parameters[i];
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
}

/**
 * check if a group is empty or not
 * @param {Object} group
 * @return {Boolean} if the group is empty or not
 */
function filterNotEmptyGroup(group: object): boolean {
  if (group.children) {
    for (let i = 0; i < group.children.length; i += 1) {
      if (group.children[i].show === true) {
        return true;
      }
    }
  }

  return false;
}

/**
 * remove groups which have no child
 * @param {Array} acc
 * @param {Object} item
 * @return {Array} accumulator of groups which are not empty
 */
function removeEmptyGroups(acc: Array, item: object): Array {
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
}

const getNavigationItems = (state: object): Array => state.navigation.items;

const menuSelector = createSelector(
  getNavigationItems,
  (navItems: Array): Array =>
    navItems.reduce(filterShowableElements, []).reduce(removeEmptyGroups, []),
);

export default menuSelector;
