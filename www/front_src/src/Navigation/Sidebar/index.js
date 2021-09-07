/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';

import clsx from 'clsx';

import NavigationMenu from './Menu';
import Logo from './Logo';
import LogoMini from './Logo/LogoMini';
import styles from './sidebar.scss';

class Sidebar extends Component {
  state = {
    active: false,
  };

  toggleNavigation = () => {
    const { active } = this.state;
    this.setState({
      active: !active,
    });
  };

  render() {
    const { navigationData, reactRoutes, style } = this.props;
    const { active } = this.state;

    return (
      <nav
        className={clsx(styles.sidebar, styles[active ? 'active' : 'mini'])}
        id="sidebar"
        style={style}
      >
        <div className={clsx(styles['sidebar-inner'])}>
          {active ? (
            <Logo onClick={this.toggleNavigation} />
          ) : (
            <LogoMini onClick={this.toggleNavigation} />
          )}
          <NavigationMenu
            navigationData={navigationData || []}
            reactRoutes={reactRoutes || {}}
            sidebarActive={active}
          />
          <div
            className={clsx(styles['sidebar-toggle-wrap'])}
            onClick={this.toggleNavigation}
          >
            <span className={clsx(styles['sidebar-toggle-icon'])} />
          </div>
        </div>
      </nav>
    );
  }
}

export default Sidebar;
