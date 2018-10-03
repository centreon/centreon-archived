import React from "react";

import { isSelected } from "../../helpers/menu";

const IconMenu = ({ items = [], selected, onSwitch }) => (
  <div class="wrap-left">
    {items.map(item => {
      return (
        <span
          class={
            "wrap-left-icon" + (isSelected(item, selected) ? " active" : "")
          }
          onClick={onSwitch.bind(this, item)}
        >
          <span class={`iconmoon icon-${item.label.toLowerCase()}`} />
        </span>
      );
    })}
  </div>
);

export default IconMenu;
