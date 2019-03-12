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
    /*
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
    */
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
        let { menuItems } = this.props;

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
      ? "/_CENTREON_PATH_PLACEHOLDER_" + entryProps.url
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
            {Object.entries(menuItems).map(([levelOneKey, levelOneProps]) => (
              levelOneProps.label ? (<li class={"menu-item" + (levelOneProps.active ? " active" : "")}>
                <span
                  onDoubleClick={() => {this.handleDoubleClick(levelOneKey, levelOneProps)}}
                  onClick={() => {this.collapseLevelTwo(levelOneKey)}}
                  style={{ cursor: "pointer" }}
                  class="menu-item-link dropdown-toggle"
                  id={"menu" + levelOneKey}
                >
                  <span class={`iconmoon icon-${levelOneProps.menu_id.toLowerCase()}`}>
                    <span class={"menu-item-name"}>
                      <Translate value={levelOneProps.label}/>
                    </span>
                  </span>
                </span>
                <ul
                  class="collapse collapsed-items list-unstyled"
                  style={{ display: (levelOneProps.toggled && active) ? "block" : "none" }}
                >
                  {Object.entries(levelOneProps.children).map(([levelTwoKey, levelTwoProps]) => {
                    const levelTwoUrl = this.getUrlFromEntry(levelTwoKey, levelTwoProps);
                    if (levelTwoProps.label) {
                      return (
                        <li
                          class={
                            "collapsed-item" +
                            (levelTwoProps.collapsed || (this.isActive(pageId, levelTwoUrl))
                              ? " active"
                              : ""
                            )
                          }
                        >
                          {Object.keys(levelTwoProps.children).length > 0 ? (
                            <span
                              style={{ cursor: "pointer" }}
                              onClick={() => {this.collapseLevelThree(levelOneKey, levelTwoKey)}}
                              class="collapsed-level-item-link"
                            >
                              <Translate value={levelTwoProps.hasOwnProperty('label') ? levelTwoProps.label : ''}/>
                            </span>
                          ) : (
                              <Link
                                onClick={() => {this.goToPage(levelTwoUrl.url, levelOneKey)}}
                                className={`collapsed-level-item-link img-none ${(this.isActive(pageId, levelTwoUrl)) ? "active" : ""}`}
                                to={levelTwoUrl.url}
                              >
                                <Translate value={levelTwoProps.label}/>
                              </Link>
                            )}

                          <ul class="collapse-level collapsed-level-items first-level list-unstyled">
                            {Object.entries(levelTwoProps.children).map(([levelThreeKey, levelThreeProps]) => {
                              return (
                                <>
                                  {Object.keys(levelTwoProps.children).length > 1 &&
                                    <span class="collapsed-level-title">
                                      <Translate value={levelThreeKey}/>
                                    </span>
                                  }
                                  {Object.entries(levelThreeProps).map(([levelFourKey, levelFourProps]) => {
                                    const levelFourUrl = this.getUrlFromEntry(levelFourKey, levelFourProps);
                                    if (levelFourProps.label) {
                                      return (
                                        <li
                                          class={"collapsed-level-item" + (this.isActive(pageId, levelFourUrl) ? " active" : "")}
                                        >
                                          <Link
                                            onClick={() => {this.goToPage(levelFourUrl.url, levelOneKey)}}
                                            className="collapsed-level-item-link"
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
          <div class="toggle-sidebar-wrap">
            <span
              class="toggle-sidebar-icon"
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
    }
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(withRouter(NavigationComponent));