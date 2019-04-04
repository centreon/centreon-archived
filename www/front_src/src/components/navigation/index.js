import React, { Component } from "react";

import logo from "../../img/centreon.png";
import miniLogo from "../../img/centreon-logo-mini.svg";

import { Link, withRouter } from "react-router-dom";
import axios from "../../axios";

import routeMap from "../../route-maps/route-map";

import { Translate } from 'react-redux-i18n';
import { setNavigation } from "../../redux/actions/navigationActions";
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
  handleDirectClick = (levelOneKey, levelOneProps) => {
    clearTimeout(this.clickTimeout)
    this.doubleClicked = true
    const urlOptions = levelOneKey.slice(1) +
      (levelOneProps.options !== null ? levelOneProps.options : '')
    this.goToPage(
      routeMap.module + "?p=" + urlOptions,
      levelOneKey
    )
  }

  // active clicked level
  activeCurrentLevel = (levelOneKey, levelTwoKey) => {
    let { menuItems } = this.state;

    Object.keys(menuItems[levelOneKey].children).forEach(subKey => {
      if (subKey === levelTwoKey) {
        menuItems[levelOneKey].children[subKey]["collapsed"] = true;
      } else {
        menuItems[levelOneKey].children[subKey]["collapsed"] = false;
      }
    });

    this.setState({
      menuItems
    });
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
    const activated = " active"

    return (
      <nav className={`sidebar ${active ? activated : " mini"}`} id="sidebar">
        <div className={`sidebar-inner`}>
          <div className={`sidebar-logo`} onClick={this.toggleNavigation}>
            <span>
              <img
                className={`sidebar-logo-image`}
                src={logo}
                width="254"
                height="57"
                alt=""
              />
            </span>
          </div>
          <div className={`sidebar-logo-mini`} onClick={this.toggleNavigation}>
            <span>
              <img
                className={`sidebar-logo-mini-image`}
                src={miniLogo}
                width="23"
                height="21"
                alt=""
              />
            </span>
          </div>
          <ul
            className={`menu menu-items list-unstyled components`}
            onMouseLeave={this.mouseLeftTheMenu}
          >
            {Object.entries(menuItems).map(([levelOneKey, levelOneProps]) => (
              levelOneProps.label ? (
                <li
                  className={`menu-item ${(levelOneProps.toggled && active || levelOneProps.active) ? activated : " to-hover"}`}
                >
                <span
                  onDoubleClick={() => {this.handleDirectClick(levelOneKey, levelOneProps)}}
                  className={`menu-item-link dropdown-toggle`}
                  id={"menu" + levelOneKey}
                >
                  <span className={`iconmoon icon-${levelOneProps.menu_id.toLowerCase()}`}>
                    <span className={`menu-item-name`}><Translate value={levelOneProps.label}/></span>
                  </span>
                </span>
                <ul
                  className={`collapse collapsed-items list-unstyled ${(levelOneProps.toggled && active) ? activated : " " }`}
                  style={{ display: (levelOneProps.toggled && active) ? "block" : "none" }}
                >
                  {Object.entries(levelOneProps.children).map(([levelTwoKey, levelTwoProps]) => {
                    const urlOptions = levelTwoKey.slice(1) +
                      (levelTwoProps.options !== null ? levelTwoProps.options : '')
                    if (levelTwoProps.label) {
                      return (
                        <li
                          className={
                           `collapsed-item ${(levelTwoProps.collapsed || (pageId == urlOptions)) ? activated : ""}`
                          }
                        >
                          {Object.keys(levelTwoProps.children).length > 0 ? (
                            <span
                              onClick={() => {this.collapseLevelThree(levelOneKey, levelTwoKey)}}
                              className={`collapsed-level-item-link`}
                            >
                              <Translate value={levelTwoProps.hasOwnProperty('label') ? levelTwoProps.label : ''}/>
                            </span>
                          ) : (
                              <Link
                                onClick={() => {
                                  this.goToPage(
                                    routeMap.module + "?p=" + urlOptions,
                                    levelOneKey
                                  )
                                }}
                                className={`collapsed-level-item-link img-none ${(pageId == urlOptions) ? activated : ""}`}
                                to={routeMap.module + "?p=" + urlOptions}
                              >
                                <Translate value={levelTwoProps.label}/>
                              </Link>
                            )}
                          <ul className={`collapse-level collapsed-level-items first-level list-unstyled`}>
                          {this.props.children}
                            {Object.entries(levelTwoProps.children).map(([levelThreeKey, levelThreeProps]) => {
                              return (
                                <React.Fragment>
                                  {Object.keys(levelTwoProps.children).length > 1 &&
                                    <span className={`collapsed-level-title`}>
                                      <Translate value={levelThreeKey}/>
                                    </span>
                                  }
                                  {Object.entries(levelThreeProps).map(([levelFourKey, levelFourProps]) => {
                                    const urlOptions = levelFourKey.slice(1) +
                                      (levelFourProps.options !== null ? levelFourProps.options : '')
                                    if (levelFourProps.label) {
                                      return (
                                        <li
                                          onClick={() => {
                                            this.activeCurrentLevel(levelOneKey, levelTwoKey)
                                          }}
                                          className={`collapsed-level-item ${pageId == urlOptions ? activated : ""}`}
                                        >
                                          <Link
                                            onClick={() => {
                                              this.goToPage(
                                                routeMap.module + "?p=" + urlOptions,
                                                levelOneKey
                                              )
                                            }}
                                            className={`collapsed-level-item-link`}
                                            to={routeMap.module + "?p=" + urlOptions}
                                          >
                                            <Translate value={levelFourProps.label}/>
                                          </Link>
                                        </li>
                                      );
                                    } else {
                                      return null
                                    }
                                  }
                                  )}
                                </React.Fragment>
                              )
                            })}
                          </ul>
                        </li>
                      );
                    } else {
                      return null
                    }
                  })}
                </ul>
              </li>) : null
            ))}
          </ul>
          <div className={`toggle-sidebar-wrap`}>
            <span
              className={`toggle-sidebar-icon`}
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
