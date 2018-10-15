import React from "react";

import routeMap from "../../route-maps/route-map";
import { Link } from "react-router-dom";

const LineMenu = ({ menu }) => (
  <div class="header-bottom" style={{ backgroundColor: menu.color }}>
    <div class="bottom-nav-wrap">
      <nav class="nav nav-wrapper">
        <ul class="nav-items">
          {menu.children
            ? Object.keys(menu.children).map(key => {
                let item = menu.children[key];
                return (
                  <li class="nav-item">
                    <span>
                      <Link
                        className={"nav-item-link"}
                        to={routeMap.module + "?p=" + key}
                      >
                        <span>{menu.children[key].label}</span>
                      </Link>
                      {Object.keys(item.children).length > 0 ? (
                        <div class="submenu">
                          <div class="submenu-inner">
                            {Object.keys(item.children).map((key, idx) => (
                              <div class={"submenu-level level-" + (idx + 1)}>
                                <h3 class="submenu-title">{key}</h3>
                                <ul class="submenu-items">
                                  {Object.keys(item.children[key]).map(
                                    childKey => {
                                      return (
                                        <li>
                                          <Link
                                            to={
                                              routeMap.module + "?p=" + childKey
                                            }
                                          >
                                            <span>
                                              {
                                                item.children[key][childKey]
                                                  .label
                                              }
                                            </span>
                                          </Link>
                                        </li>
                                      );
                                    }
                                  )}
                                </ul>
                              </div>
                            ))}
                          </div>
                        </div>
                      ) : null}
                    </span>
                  </li>
                );
              })
            : null}
        </ul>
      </nav>
    </div>
  </div>
);

export default LineMenu;
