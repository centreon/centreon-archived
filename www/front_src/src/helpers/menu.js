export const isSelected = (item, selected) => {
    if (item && selected) {
      return item.label == selected.label;
    }
  };