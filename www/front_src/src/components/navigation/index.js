import React, { Component } from "react";
import { connect } from "react-redux";

import logo from "../../img/centreon.png";
import miniLogo from "../../img/centreon-logo-mini.svg";

import { Link, withRouter } from "react-router-dom";
import axios from "../../axios";

import routeMap from "../../route-maps/route-map";

import { Translate } from 'react-redux-i18n';
import { fetchNavigationData } from "../../redux/actions/navigationActions";

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

  componentDidMount = () => {
    const { fetchNavigationData } = this.props;
    fetchNavigationData();
  };

  // toggle between icons menu and details menu
  toggleNavigation = () => {
    const { active } = this.state;
    this.setState({
      active: !active
    });
  };

  // handle direct click on level 1
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
      menuItems[levelOneKey].children[subKey]["collapsed"] = false;
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
    let { menuItems } = this.props;

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
    let { menuItems } = this.props;

    Object.keys(menuItems).forEach(key => {
      menuItems[key].active = (key === index);
    })

    this.setState({
      menuItems
    });
  };

  // check if current tab is active
  isActive = (pageId, urlParams) => {
    let isActive = false;
    if (urlParams.url.match(/main\.php/)) { // legacy url
      isActive = pageId == urlParams.urlOptions;
    } else { // react route
      isActive = pageId == urlParams.url;
    }

    return isActive;
  };

  // get page id
  // legacy routes ==> get topology page
  // react routes ==> get path (eg: /administration/extensions/manager)
  getPageId = () => {
    const { pathname, search } = this.props.history.location;
    let pageId = '';
    if (search.match(/p=/)) { // legacy url
      pageId = search.split("p=")[1];
    } else { // react route
      pageId = pathname;
    }
    return pageId;
  }

  // get url parameters from navigation entry
  // eg: {url: '/administration/extensions/manager', urlOptions: ''}
  // eg: {url: 'main.php?p=570101&o=c', urlOptions: '&o=c'}
  getUrlFromEntry = (entryKey, entryProps) => {
    const urlOptions = entryKey.slice(1) + (entryProps.options !== null ? entryProps.options : '');
    const url = entryProps.is_react == '1'
      ? entryProps.url
      : routeMap.module + "?p=" + urlOptions;
    return { url, urlOptions };
  }

  // navigate to the page
  goToPage = (route, topLevelIndex) => {
    const { history } = this.props;
    this.activateTopLevelMenu(topLevelIndex);
    history.push(route);
  };


  render() {
    const { menuItems } = this.props;
    const { active } = this.state;
    const pageId = this.getPageId();
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
          <div className={`sidebar-logo-mini`}  onClick={this.toggleNavigation}>
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
                  <span class={`iconmoon icon-${levelOneProps.menu_id.toLowerCase()}`}>
                    <span className={`menu-item-name`}>
                      <Translate value={levelOneProps.label}/>
                    </span>
                  </span>
                </span>
                <ul
                  className={`collapse collapsed-items list-unstyled ${(levelOneProps.toggled && active) ? activated : " " }`}
                  style={{ display: (levelOneProps.toggled && active) ? "block" : "none" }}
                >
                  {Object.entries(levelOneProps.children).map(([levelTwoKey, levelTwoProps]) => {
                    const levelTwoUrl = this.getUrlFromEntry(levelTwoKey, levelTwoProps);
                    if (levelTwoProps.label) {
                      return (
                        <li
                          className={
                            `collapsed-item
                            ${levelTwoProps.collapsed || (this.isActive(pageId, levelTwoUrl))
                            ?
                              activated
                            :
                              ""}`
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
                                onClick={() => {this.goToPage(levelTwoUrl.url, levelOneKey)}}
                                className={`collapsed-level-item-link img-none ${(this.isActive(pageId, levelTwoUrl)) ? activated : ""}`}
                                to={levelTwoUrl.url}
                              >
                                <Translate value={levelTwoProps.label}/>
                              </Link>
                            )}

                          <ul className={`collapse-level collapsed-level-items first-level list-unstyled`}>
                            {Object.entries(levelTwoProps.children).map(([levelThreeKey, levelThreeProps]) => {
                              return (
                                <>
                                  {Object.keys(levelTwoProps.children).length > 1 &&
                                    <span className={`collapsed-level-title`}>
                                      <Translate value={levelThreeKey}/>
                                    </span>
                                  }
                                  {Object.entries(levelThreeProps).map(([levelFourKey, levelFourProps]) => {
                                    const levelFourUrl = this.getUrlFromEntry(levelFourKey, levelFourProps);
                                    if (levelFourProps.label) {
                                      return (
                                        <li
                                          onClick={() => {
                                            active ?
                                              this.activeCurrentLevel(levelOneKey, levelTwoKey)
                                            : 
                                              this.collapseLevelThree(levelOneKey, levelTwoKey)
                                          }}
                                          className={`collapsed-level-item ${this.isActive(pageId, levelFourUrl)  ? activated : ""}`}
                                        >
                                          <Link
                                            onClick={() => {this.goToPage(levelFourUrl.url, levelOneKey)}}
                                            className={`collapsed-level-item-link`}
                                            to={levelFourUrl.url}
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
                                </>
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
              onClick={this.toggleNavigation}
            />
          </div>
        </div>
      </nav>
    );
  }
}

const mapStateToProps = ({ navigation }) => ({
  entries: navigation.entries,
  menuItems: navigation.menuItems
});

const mapDispatchToProps = dispatch => {
  return {
    fetchNavigationData: () => {
      dispatch(fetchNavigationData());
    },
    updateTooltip: () => {
      dispatch(updateTooltip());
    }
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(withRouter(NavigationComponent));