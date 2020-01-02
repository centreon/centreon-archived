/* eslint-disable consistent-return */
/* eslint-disable import/prefer-default-export */

export const isSelected = (item: object, selected: object) => {
  if (item && selected) {
    return item.label === selected.label;
  }
};
