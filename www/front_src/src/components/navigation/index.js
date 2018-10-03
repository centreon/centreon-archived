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
    for (let i = 0; i < menuItems.length; i++) {
      menuItems[i].toggled = false;
    }
    menuItems[index].toggled = menuItems[index].toggled ? false : true;
    this.setState({
      active: true,
      menuItems
    });
  };

  handleDoubleClickItem = index => {
    switch (index) {
      case 1:
        this.goToPage('main.php?p=20201', 1);
        break;
      case 2:
        this.goToPage('main.php?p=30701', 2);
        break;
      case 3:
        this.goToPage('main.php?p=60101', 3);
        break;
      case 4:
        this.goToPage('main.php?p=50110&o=general', 4);
        break;
      default:
        this.goToPage('main.php?p=103', 0);
        break;
    };
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
      menuItems
    });
  };

  collapseSubMenu = (key, index) => {
    let { menuItems } = this.state;

    if(menuItems[index].children[key]["collapsed"]) {
      menuItems[index].children[key]["collapsed"] = false;
    } else {
      Object.keys(menuItems[index].children).forEach(subKey => {
        menuItems[index].children[subKey]["collapsed"] = key === subKey ? true : false;
      });
    }

    this.setState({
      menuItems
    });
  };

  activateTopLevelMenu = index => {
    let { menuItems } = this.state;
    for (let i = 0; i < menuItems.length; i++) {
      menuItems[i].active = false;
    }
    menuItems[index].active = true;
    this.setState({
      menuItems
    });
  };

  goToPage = (route, topLevelIndex) => {
    const { history } = this.props;
    this.activateTopLevelMenu(topLevelIndex);
    history.push(route);
  };

  render() {
    const { active, menuItems, initiallyCollapsed } = this.state;
    const pageId = this.props.location.search.split("=")[1];
    return (
      <nav class={"sidebar" + (active ? " active" : "")} id="sidebar">
        <div class="sidebar-inner">
          <div class="sidebar-logo" onClick={this.toggleNavigation.bind(this)}>
            <span>
              <img
                class="sidebar-logo-image"
                src={logo}
                width="254"
                height="57"
                alt=""
              />
            </span>
          </div>
          <div class="sidebar-logo-mini" onClick={this.toggleNavigation.bind(this)}>
            <span>
              <img
                class="sidebar-logo-mini-image"
                src={miniLogo}
                width="23"
                height="21"
                alt=""
              />
            </span>
          </div>
          <ul class="menu menu-items list-unstyled components">
            {menuItems.map((item, index) => {
              return (
                <li class={"menu-item" + (item.active ? " active" : "")}>
                  <span
                    onDoubleClick={this.handleDoubleClickItem.bind(this,index)}
                    onClick={this.onSwitch.bind(this, index)}
                    style={{ cursor: "pointer" }}
                    class="menu-item-link dropdown-toggle"
                    id={"menu"+ index}
                  >
                    <span class={`iconmoon icon-${item.label.toLowerCase()}`}>
                      <span class={"menu-item-name"}>{item.label}</span>
                    </span>
                  </span>
                  <ul
                    class="collapse collapsed-items list-unstyled"
                    style={{ display: (item.toggled && active) ? "block" : "none" }}
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
                                <span
                                  style={{ cursor: "pointer" }}
                                  onClick={this.collapseSubMenu.bind(
                                    this,
                                    menuKey,
                                    index
                                  )}
                                  class="collapsed-level-item-link"
                                >
                                  {subItem.label}
                                </span>
                              ) : (
                                <Link
                                  className="collapsed-level-item-link img-none"
                                  to={routeMap.module + "?p=" + menuKey.slice(1) + (subItem.options ? subItem.options : '')}
                                >
                                  {subItem.label}
                                </Link>
                              )}

                              <ul class="collapse-level collapsed-level-items first-level list-unstyled">
                                {Object.keys(subItem.children).length > 0
                                  ? 
                                  Object.keys(subItem.children).map(
                                      (key, idx) => (
                                        <React.Fragment>
                                          {
                                          Object.keys(subItem.children).length > 1 ? 
                                            <span class="collapsed-level-title">
                                              {key}:{" "}
                                            </span> 
                                          : null
                                          }
                                          {Object.keys(subItem.children[key]).map(
                                            subKey => {
                                              const urlOptions = subItem.children[key][subKey].options !== null
                                                ? subItem.children[key][subKey].options
                                                : ''
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
                                                  <Link
                                                    onClick={this.goToPage.bind(
                                                      this,
                                                      routeMap.module +
                                                        "?p=" +
                                                        subKey.slice(1) +
                                                        urlOptions,
                                                      index
                                                    )}
                                                    className="collapsed-level-item-link"
                                                    to={routeMap.module + "?p=" + subKey.slice(1) + urlOptions}
                                                  >
                                                    {subItem.children[key][subKey].label}
                                                  </Link>
                                                </li>
                                              );
                                            }
                                          )}
                                        </React.Fragment>
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
          <div class="toggle-sidebar-wrap">
            <span
              class="toggle-sidebar-icon"
              onClick={this.toggleNavigation.bind(this)}
            />
          </div>
        </div>
      </nav>
    );
  }
}

export default withRouter(NavigationComponent);
