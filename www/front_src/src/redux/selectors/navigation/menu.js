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

function removeEmptyGroups(acc, item) {
  if (item.children) {
    return [
      ...acc,
      {
        ...item,
        children: item.children.reduce(removeEmptyGroups, []),
      }
    ];
  }

  if (item.groups) {
    return [
      ...acc,
      {
        ...item,
        groups: item.groups.filter(filterNotEmptyGroup),
      }
    ];
  }

  return [
    ...acc,
    item
  ];
}

function filterNotEmptyGroup(group) {
  if (group.children) {
    for (const child of group.children) {
      if (child.show === true) {
        return true;
      }
    }
  }

  return false;
}

const getNavigationItems = (state) => state.navigation.items;

export const menuSelector = createSelector(
  getNavigationItems,
  (navItems) => navItems.reduce(filterShowableElements, []).reduce(removeEmptyGroups, []),
);