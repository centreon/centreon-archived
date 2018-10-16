import React, { Component } from "react";

import logo from "../../img/centreon.png";
import miniLogo from "../../img/centreon-logo-mini.svg";

import { Link, withRouter } from "react-router-dom";
import axios from "../../axios";

import routeMap from "../../route-maps/route-map";

import {Translate} from 'react-redux-i18n';
import { setNavigation } from"../../redux/actions/navigationActions";
import { connect } from "react-redux";


class NavigationComponent extends Component {
  navService = axios("internal.php?object=centreon_menu&action=menu");

  clickTimeout = null;
  doubleClicked = false;

  state = {
    active: false,
    initiallyCollapsed: false,
    selectedMenu: {},
    menuItems: []
  };

  UNSAFE_componentWillMount = () => {
    const { setNavigation } = this.props

    this.navService.get().then(({ data }) => {

      // store allowed topologies in redux (useful to get acl information in other components)
      setNavigation(data);

      // provide data in the state (render menu)
      this.setState({
        menuItems: data,
        selectedMenu: Object.values(data)[0]
      });
    })
  };

  // toggle between icons menu and details menu
  toggleNavigation = () => {
    const { active } = this.state;
    this.setState({
      active: !active
    });
  };

  // handle double click on level 1
  handleDoubleClick = (levelOneKey, levelOneProps) => {
    clearTimeout(this.clickTimeout)
    this.doubleClicked = true
    const urlOptions = levelOneKey.slice(1) +
      (levelOneProps.options !== null ? levelOneProps.options : '')
    this.goToPage(
      routeMap.module + "?p=" + urlOptions,
      levelOneKey
    )
  }

  // display/hide level 2
  collapseLevelTwo = index => {
    this.clickTimeout = setTimeout(() => {
      if (!this.doubleClicked) {
        let { menuItems } = this.state;

        Object.keys(menuItems).forEach(key => {
          menuItems[key].toggled = key === index ?
            !menuItems[index].toggled : false;
        });

        this.setState({
          active: true,
          menuItems
        });
      }
      this.doubleClicked = false
    }, 200);
  };

  // display/hide level 3
  collapseLevelThree = (levelOneKey, levelTwoKey) => {
    let { menuItems } = this.state;

    Object.keys(menuItems[levelOneKey].children).forEach(subKey => {
      if (subKey === levelTwoKey) {
        menuItems[levelOneKey].children[subKey]["collapsed"] = !menuItems[levelOneKey].children[subKey]["collapsed"];
      } else {
        menuItems[levelOneKey].children[subKey]["collapsed"] = false;
      }
    });

    this.setState({
      menuItems
    });
  };

  // activate level 1 (display colored menu)
  activateTopLevelMenu = index => {
    let { menuItems } = this.state;

    Object.keys(menuItems).forEach(key => {
      menuItems[key].active = (key === index);
    })

    this.setState({
      menuItems
    });
  };

  // navigate to the page
  goToPage = (route, topLevelIndex) => {
    const { history } = this.props;
    this.activateTopLevelMenu(topLevelIndex);
    history.push(route);
  };

  render() {
    const { active, menuItems } = this.state;
    const pageId = this.props.history.location.search.split("p=")[1];

    return (
      <nav class={"sidebar" + (active ? " active" : "")} id="sidebar">
        <div class="sidebar-inner">
          <div class="sidebar-logo" onClick={this.toggleNavigation}>
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
          <div class="sidebar-logo-mini" onClick={this.toggleNavigation}>
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
            {Object.entries(menuItems).map(([levelOneKey, levelOneProps]) => {
              return (
                <li class={"menu-item" + (levelOneProps.active ? " active" : "")}>
                  <span
                    onDoubleClick={() => {this.handleDoubleClick(levelOneKey, levelOneProps)}}
                    onClick={() => {this.collapseLevelTwo(levelOneKey)}}
                    style={{ cursor: "pointer" }}
                    class="menu-item-link dropdown-toggle"
                    id={"menu" + levelOneKey}
                  >
                    <span class={`iconmoon icon-${levelOneProps.menu_id.toLowerCase()}`}>
                      <span class={"menu-item-name"}><Translate value={levelOneProps.label}/></span>
                    </span>
                  </span>
                  <ul
                    class="collapse collapsed-items list-unstyled"
                    style={{ display: (levelOneProps.toggled && active) ? "block" : "none" }}
                  >
                    {Object.entries(levelOneProps.children).map(([levelTwoKey, levelTwoProps]) => {
                      const urlOptions = levelTwoKey.slice(1) +
                        (levelTwoProps.options !== null ? levelTwoProps.options : '')
                      return (
                        <li
                          class={
                            "collapsed-item" + (levelTwoProps.collapsed || (pageId == urlOptions) ? " active" : "")
                          }
                        >
                          {Object.keys(levelTwoProps.children).length > 0 ? (
                            <span
                              style={{ cursor: "pointer" }}
                              onClick={() => {this.collapseLevelThree(levelOneKey, levelTwoKey)}}
                              class="collapsed-level-item-link"
                            >
                              <Translate value={levelTwoProps.label}/>
                            </span>
                          ) : (
                            <Link
                              onClick={() => {
                                this.goToPage(
                                  routeMap.module + "?p=" + urlOptions,
                                  levelOneKey
                                )
                              }}
                              className="collapsed-level-item-link img-none"
                              to={routeMap.module + "?p=" + urlOptions}
                            >
                              <Translate value={levelTwoProps.label}/>
                            </Link>
                          )}

                          <ul class="collapse-level collapsed-level-items first-level list-unstyled">
                            {Object.entries(levelTwoProps.children).map(([levelThreeKey, levelThreeProps]) => {
                              return (
                              <React.Fragment>
                                {Object.keys(levelTwoProps.children).length > 1 &&
                                  <span class="collapsed-level-title">
                                    <Translate value={levelThreeKey}/>
                                  </span>
                                }
                                {Object.entries(levelThreeProps).map(([levelFourKey, levelFourProps]) => {
                                    const urlOptions = levelFourKey.slice(1) +
                                      (levelFourProps.options !== null ? levelFourProps.options : '')
                                    return (
                                      <li
                                        class={"collapsed-level-item" + (pageId == urlOptions ? " active" : "")}
                                      >
                                        <Link
                                          onClick={() => {
                                            this.goToPage(
                                              routeMap.module + "?p=" + urlOptions,
                                              levelOneKey
                                            )
                                          }}
                                          className="collapsed-level-item-link"
                                          to={routeMap.module + "?p=" + urlOptions}
                                        >
                                          <Translate value={levelFourProps.label}/>
                                        </Link>
                                      </li>
                                    );
                                  }
                                )}
                              </React.Fragment>
                            )})}
                          </ul>
                        </li>
                      );
                    })}
                  </ul>
                </li>
              );
            })}
          </ul>
          <div class="toggle-sidebar-wrap">
            <span
              class="toggle-sidebar-icon"
              onClick={() => {this.toggleNavigation()}}
            />
          </div>
        </div>
      </nav>
    );
  }
}

const mapStateToProps = () => {}

const mapDispatchToProps = {
  setNavigation
};

export default connect(mapStateToProps, mapDispatchToProps)(withRouter(NavigationComponent));
