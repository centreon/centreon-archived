/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import { Component } from 'react';

import clsx from 'clsx';

import ArrowForwardIcon from '@mui/icons-material/ArrowForwardIos';
import ArrowBackIcon from '@mui/icons-material/ArrowBackIos';

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

    const arrowProps = {
      fontSize: 'small',
    };

    return (
      <nav
        className={clsx(styles.sidebar, styles[active ? 'active' : 'mini'])}
        id="sidebar"
        style={style}
      >
        <div
          style={{
            display: 'grid',
            gridTemplateRows: 'auto 1fr auto',
            height: '100%',
          }}
        >
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
            style={{
              cursor: 'pointer',
              display: 'flex',
              justifyContent: active ? 'flex-end' : 'center',
            }}
            onClick={this.toggleNavigation}
          >
            {active ? (
              <ArrowBackIcon {...arrowProps} />
            ) : (
              <ArrowForwardIcon {...arrowProps} />
            )}
          </div>
        </div>
      </nav>
    );
  }
}

export default Sidebar;
