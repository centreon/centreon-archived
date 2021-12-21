/* eslint-disable class-methods-use-this */
/* eslint-disable jsx-a11y/mouse-events-have-key-events */
/* eslint-disable no-useless-concat */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/anchor-is-valid */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable prefer-destructuring */
/* eslint-disable no-restricted-globals */
/* eslint-disable no-plusplus */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';

import clsx from 'clsx';
import { Link as RouterLink } from 'react-router-dom';
import { withRouter } from 'react-router';

import Link from '@mui/material/Link';
import { Typography, styled } from '@mui/material';
import ArrowForwardIcon from '@mui/icons-material/ArrowForwardIos';

import BoundingBox from './BoundingBox';
import styles from './menu.scss';
import icons from './icons';

const StyledLink = styled(Link)(() => ({
  textDecoration: 'none',
}));

class NavigationMenu extends Component {
  state = {
    activeSecondLevel: null,
    doubleClickedLevel: null,
    hoveredFirstLevel: null,
    hrefOfIframe: false,
    navigatedPageId: false,
  };

  componentDidMount() {
    window.addEventListener('react.href.update', this.watchHrefChange, false);
  }

  componentWillUnmount() {
    window.removeEventListener('react.href.update', this.watchHrefChange);
  }

  watchHrefChange = (event) => {
    if (event.detail.href.match(/p=/)) {
      this.setState({
        hrefOfIframe: event.detail.href,
      });
    }
  };

  getUrlFromEntry = (entryProps) => {
    const urlOptions =
      entryProps.page + (entryProps.options !== null ? entryProps.options : '');
    const url = entryProps.is_react
      ? entryProps.url
      : `/main.php?p=${urlOptions}`;

    return url;
  };

  activateSecondLevel = (secondLevelPage) => {
    const { activeSecondLevel } = this.state;

    this.setState({
      activeSecondLevel:
        activeSecondLevel === secondLevelPage ? true : secondLevelPage,
    });
  };

  getActiveTopLevelIndex = (pageId) => {
    const { navigationData } = this.props;
    let index = -1;
    for (let i = 0; i < navigationData.length; i++) {
      if (
        !isNaN(pageId) &&
        String(pageId).charAt(0) === navigationData[i].page
      ) {
        index = i;
      }
    }

    return index;
  };

  areSamePage = (page, level, imersion) => {
    return imersion
      ? !isNaN(page) && String(page).substring(0, imersion) === level.page
      : !isNaN(page) && page === level.page;
  };

  render() {
    const { navigationData, sidebarActive, reactRoutes, history } = this.props;
    const {
      activeSecondLevel,
      doubleClickedLevel,
      navigatedPageId,
      hrefOfIframe,
    } = this.state;
    let pageId = '';
    const { pathname, search } = window.location;

    if (navigatedPageId && !hrefOfIframe) {
      pageId = navigatedPageId;
    } else if (hrefOfIframe) {
      if (hrefOfIframe.match(/p=/)) {
        pageId = hrefOfIframe.split('p=')[1];
        if (pageId) {
          pageId = pageId.split('&')[0];
        }
      } else {
        pageId = reactRoutes[hrefOfIframe] || hrefOfIframe;
      }
    } else if (search.match(/p=/)) {
      pageId = search.split('p=')[1].split('&')[0];
    } else {
      pageId = reactRoutes[pathname] || pathname;
    }

    const activeIndex = this.getActiveTopLevelIndex(pageId);

    return (
      <ul
        className={clsx(
          styles.menu,
          styles['menu-items'],
          styles['list-unstyled'],
          styles[sidebarActive ? 'menu-big' : 'menu-small'],
        )}
      >
        {navigationData.map((firstLevel, firstLevelIndex) => {
          const firstLevelIsActive =
            firstLevel.toggled || this.areSamePage(pageId, firstLevel, 1);

          const MenuIcon = icons[firstLevel.icon];

          const { hoveredFirstLevel } = this.state;

          const isFirstLevelHovered = hoveredFirstLevel === firstLevel.label;

          const isFirstLevelHighlited =
            isFirstLevelHovered || firstLevelIsActive;

          return (
            <li
              className={clsx(
                styles['menu-item'],
                styles[`color-${firstLevel.color}`],
                {
                  [styles.active]: firstLevelIsActive,
                  [styles[`active-${firstLevel.color}`]]: firstLevelIsActive,
                },
              )}
              key={`firstLevel-${firstLevel.page}`}
            >
              <span
                className={clsx(styles['menu-item-link'])}
                style={{ width: '100%' }}
              >
                <StyledLink
                  className={clsx(styles[`icon-${firstLevel.icon}`])}
                  component={RouterLink}
                  to={this.getUrlFromEntry(firstLevel)}
                  onClick={(e) => {
                    if (doubleClickedLevel) {
                      this.setState({
                        doubleClickedLevel: null,
                        hrefOfIframe: false,
                      });
                    } else {
                      e.preventDefault();
                    }
                  }}
                  onDoubleClick={() => {
                    this.setState(
                      {
                        doubleClickedLevel: firstLevel,
                        hrefOfIframe: false,
                      },
                      () => {
                        history.push(this.getUrlFromEntry(firstLevel));
                      },
                    );
                  }}
                >
                  <span
                    className={clsx(styles['menu-item'])}
                    style={{
                      alignItems: 'center',
                      display: 'flex',
                      paddingLeft: 4,
                    }}
                    onMouseLeave={() =>
                      this.setState({
                        hoveredFirstLevel: null,
                      })
                    }
                    onMouseOver={() =>
                      this.setState({
                        hoveredFirstLevel: firstLevel.label,
                      })
                    }
                  >
                    <MenuIcon
                      style={{
                        color: isFirstLevelHighlited
                          ? '#FFFFFF'
                          : `#${firstLevel.color}`,
                        fontSize: 28,

                        padding: 4,
                      }}
                    />
                    {sidebarActive && (
                      <Typography
                        color="textPrimary"
                        style={{
                          color: isFirstLevelHighlited ? '#FFFFFF' : undefined,
                        }}
                      >
                        {firstLevel.label}
                      </Typography>
                    )}
                  </span>
                </StyledLink>
              </span>
              <ul
                className={clsx(
                  styles.collapse,
                  styles['collapsed-items'],
                  styles['list-unstyled'],
                  styles[`border-${firstLevel.color}`],
                  styles[
                    activeIndex !== -1 &&
                    firstLevelIndex > activeIndex &&
                    sidebarActive &&
                    navigationData[activeIndex].children.length >= 5
                      ? 'towards-down'
                      : 'towards-up'
                  ],
                )}
              >
                {firstLevel.children.map((secondLevel) => {
                  const secondLevelIsActive =
                    activeSecondLevel === secondLevel.page ||
                    (!activeSecondLevel &&
                      this.areSamePage(pageId, secondLevel, 3));
                  const secondLevelIsColored =
                    secondLevel.toggled ||
                    this.areSamePage(pageId, secondLevel, 3);

                  return (
                    <li
                      className={clsx(styles['collapsed-item'], {
                        [styles.active]: secondLevelIsActive,
                        [styles[`active-${firstLevel.color}`]]:
                          secondLevelIsColored,
                      })}
                      key={`secondLevel-${secondLevel.page}`}
                    >
                      <StyledLink
                        className={clsx(
                          styles['collapsed-item-level-link'],
                          styles[`color-${firstLevel.color}`],
                          {
                            [styles['img-none']]: secondLevel.groups.length < 1,
                          },
                        )}
                        component={RouterLink}
                        to={this.getUrlFromEntry(secondLevel)}
                        onClick={(e) => {
                          if (secondLevel.groups.length > 0) {
                            e.preventDefault(); // do not redirect if level 2 has children
                            this.activateSecondLevel(secondLevel.page);
                          } else {
                            this.setState({
                              hrefOfIframe: false,
                              navigatedPageId: secondLevel.page,
                            });
                          }
                        }}
                      >
                        <div
                          style={{
                            alignItems: 'center',
                            display: 'grid',
                            gridGap: 8,
                            gridTemplateColumns: '1fr 10fr',
                            justifyContent: 'flex-start',
                            width: '100%',
                          }}
                        >
                          {secondLevel.groups.length > 0 ? (
                            <ArrowForwardIcon style={{ fontSize: 11 }} />
                          ) : (
                            <span />
                          )}
                          <Typography style={{ fontSize: 11 }}>
                            {secondLevel.label}
                          </Typography>
                        </div>
                      </StyledLink>
                      <BoundingBox active>
                        {({ rectBox }) => {
                          let styleFor3rdLevel = {};
                          if (rectBox && rectBox.bottom < 1) {
                            styleFor3rdLevel = {
                              height: rectBox.offsetHeight + rectBox.bottom,
                            };
                          }

                          return (
                            <ul
                              className={clsx(
                                styles['collapsed-level-items'],
                                styles['list-unstyled'],
                              )}
                              style={styleFor3rdLevel}
                            >
                              {secondLevel.groups.map((group) => (
                                <React.Fragment
                                  key={`thirdLevelFragment-${group.label}`}
                                >
                                  {secondLevel.groups.length > 1 ? (
                                    <Typography
                                      className={clsx(
                                        styles['collapsed-level-title'],
                                      )}
                                      variant="body1"
                                    >
                                      {group.label}
                                    </Typography>
                                  ) : null}
                                  {group.children.map((thirdLevel) => {
                                    const thirdLevelIsActive =
                                      thirdLevel.toggled ||
                                      this.areSamePage(pageId, thirdLevel);

                                    return (
                                      <li
                                        className={clsx(
                                          styles['collapsed-level-item'],
                                          {
                                            [styles.active]: thirdLevelIsActive,
                                            [styles[
                                              `active-${firstLevel.color}`
                                            ]]: thirdLevelIsActive,
                                          },
                                        )}
                                        key={`thirdLevel-${thirdLevel.page}`}
                                      >
                                        <StyledLink
                                          className={clsx(
                                            styles['collapsed-item-level-link'],
                                            styles[`color-${firstLevel.color}`],
                                          )}
                                          component={RouterLink}
                                          to={this.getUrlFromEntry(thirdLevel)}
                                          onClick={() => {
                                            this.setState({
                                              hrefOfIframe: false,
                                              navigatedPageId: thirdLevel.page,
                                            });
                                          }}
                                        >
                                          <Typography style={{ fontSize: 11 }}>
                                            {thirdLevel.label}
                                          </Typography>
                                        </StyledLink>
                                      </li>
                                    );
                                  })}
                                </React.Fragment>
                              ))}
                            </ul>
                          );
                        }}
                      </BoundingBox>
                    </li>
                  );
                })}
              </ul>
            </li>
          );
        })}
      </ul>
    );
  }
}

export default withRouter(NavigationMenu);
