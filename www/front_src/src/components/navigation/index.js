import React, { Component } from "react";

import logo from "../../img/centreon.png";
import miniLogo from "../../img/centreon-logo-mini.svg";

import { Link, withRouter } from "react-router-dom";
import axios from "../../axios";

import routeMap from "../../route-maps/index";
class NavigationComponent extends Component {
  navService = axios("internal.php?object=centreon_menu&action=menu");

  state = {
    active: false,
    initiallyCollapsed: false,
    selectedMenu: {},
    menuItems: []
  };

  switchTopLevelMenu = selectedMenu => {
    this.setState({
      selectedMenu
    });
  };

  transformToArray = (data, callback) => {
    let result = [];
    for (var key in data) {
      result.push(data[key]);
    }
    callback(result);
  };

  onSwitch = index => {
    let { menuItems } = this.state;
    menuItems[index].toggled = menuItems[index].toggled ? false : true;
    this.setState({
      active:false,
      menuItems
    });
  };

  UNSAFE_componentWillMount = () => {
    this.navService.get().then(({ data }) => {
      this.transformToArray(data, array => {
        this.setState({
          menuItems: array,
          selectedMenu: array[0]
        });
      });
    });
  };

  toggleNavigation = () => {
    const { active } = this.state;
    this.setState({
      active: !active
    });
  };

  collapseInitialSubMenu = (key, index) => {
    let { menuItems } = this.state;
    menuItems[index].toggled = true;
    menuItems[index].children[key]["collapsed"] = menuItems[index].children[
      key
    ]["collapsed"]
      ? false
      : true;
    this.setState({
      initiallyCollapsed: true,
      menuItems: menuItems
    });
  };

  collapseSubMenu = (key, index) => {
    let { menuItems } = this.state;
    menuItems[index].children[key]["collapsed"] = menuItems[index].children[
      key
    ]["collapsed"]
      ? false
      : true;
    this.setState({
      menuItems: menuItems
    });
  };

  activateTopLevelMenu = index => {
    let { menuItems } = this.state;
    for (let i = 0; i < menuItems.length; i++) {
      menuItems[i].active = false;
    }
    menuItems[index].active = true;
    this.setState({
      menuItems: menuItems
    });
  };

  goToPage = (route, topLevelIndex) => {
    const { history } = this.props;
    this.activateTopLevelMenu(topLevelIndex);
    history.push(route);
  };

  render() {
    const { active, menuItems, selectedMenu, initiallyCollapsed } = this.state;
    const pageId = this.props.location.search.split("=")[1];
    return (
      <nav class={"sidebar" + (active ? " active" : "")} id="sidebar">
        <div class="sidebar-inner">
          <div class="sidebar-logo">
            <a onClick={this.toggleNavigation.bind(this)}>
              <img
                class="sidebar-logo-image"
                src={logo}
                width="254"
                height="57"
                alt=""
              />
            </a>
          </div>
          <div class="sidebar-logo-mini">
            <a onClick={this.toggleNavigation.bind(this)}>
              <img
                class="sidebar-logo-mini-image"
                src={miniLogo}
                width="23"
                height="21"
                alt=""
              />
            </a>
          </div>
          <ul class="menu menu-items list-unstyled components">
            {menuItems.map((item, index) => {
              return (
                <li class={"menu-item" + (item.active ? " active" : "")}>
                  <a
                    onClick={this.onSwitch.bind(this, index)}
                    style={{ cursor: "pointer" }}
                    class="menu-item-link dropdown-toggle"
                  >
                    <span class={`iconmoon icon-${item.label.toLowerCase()}`}>
                      <span class={"menu-item-name"}>{item.label}</span>
                    </span>
                  </a>
                  <ul
                    class="collapse collapsed-items list-unstyled"
                    style={{ display: (item.toggled && !active) ? "block" : "none" }}
                  >
                    {item.children
                      ? Object.keys(item.children).map(menuKey => {
                          let subItem = item.children[menuKey];
                          return (
                            <li
                              class={
                                "collapsed-item" +
                                (subItem.collapsed ? " active" : "")
                              }
                            >
                              {Object.keys(subItem.children).length > 0 ? (
                                <a
                                  style={{ cursor: "pointer" }}
                                  onClick={this.collapseSubMenu.bind(
                                    this,
                                    menuKey,
                                    index
                                  )}
                                  class="collapsed-level-item-link"
                                >
                                  {subItem.label}
                                </a>
                              ) : (
                                <Link
                                  className="collapsed-level-item-link img-none"
                                  to={routeMap.module + "?p=" + menuKey}
                                >
                                  {subItem.label}
                                </Link>
                              )}

                              <ul class="collapse-level collapsed-level-items first-level list-unstyled">
                                {Object.keys(subItem.children).length > 0
                                  ? Object.keys(subItem.children).map(
                                      (key, idx) => (
                                        <div>
                                          <span class="collapsed-level-title">
                                            {key}:{" "}
                                          </span>
                                          {Object.keys(subItem.children[key]).map(
                                            (subKey, idx) => {
                                              if (pageId == subKey) {
                                                if (!initiallyCollapsed) {
                                                  this.collapseInitialSubMenu(
                                                    menuKey,
                                                    index
                                                  );
                                                  this.activateTopLevelMenu(
                                                    index
                                                  );
                                                }
                                              }
                                              return (
                                                <li
                                                  class={
                                                    "collapsed-level-item" +
                                                    (pageId == subKey
                                                      ? " active"
                                                      : "")
                                                  }
                                                >
                                                  <a
                                                    onClick={this.goToPage.bind(
                                                      this,
                                                      routeMap.module +
                                                        "?p=" +
                                                        subKey,
                                                      index
                                                    )}
                                                    className="collapsed-level-item-link"
                                                  >
                                                    {
                                                      subItem.children[key][
                                                        subKey
                                                      ].label
                                                    }
                                                  </a>
                                                </li>
                                              );
                                            }
                                          )}
                                        </div>
                                      )
                                    )
                                  : null}
                              </ul>
                            </li>
                          );
                        })
                      : null}
                  </ul>
                </li>
              );
            })}
          </ul>
          <span
            class="toggle-sidebar"
            onClick={this.toggleNavigation.bind(this)}
          />
        </div>
      </nav>
    );
  }
}

export default withRouter(NavigationComponent);
