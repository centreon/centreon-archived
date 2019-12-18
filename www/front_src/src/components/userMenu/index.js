/* eslint-disable react/button-has-type */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable no-return-assign */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/destructuring-assignment */

import React, { Component } from 'react';
import classnames from 'classnames';
import { Translate } from 'react-redux-i18n';
import { connect } from 'react-redux';
import { Link } from 'react-router-dom';
import { allowedPagesSelector } from '../../redux/selectors/navigation/allowedPages';
import styles from '../header/header.scss';
import Clock from '../clock';
import axios from '../../axios';

const EDIT_PROFILE_TOPOLOGY_PAGE = '50104';

class UserMenu extends Component {
  userService = axios('internal.php?object=centreon_topcounter&action=user');

  refreshTimeout = null;

  state = {
    toggled: false,
    copied: false,
    data: null,
  };

  componentDidMount() {
    window.addEventListener('mousedown', this.handleClick, false);
    this.getData();
  }

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
    clearTimeout(this.refreshTimeout);
  }

  // fetch api to get user data
  getData = () => {
    this.userService
      .get()
      .then(({ data }) => {
        this.setState(
          {
            data,
          },
          this.refreshData,
        );
      })
      .catch((error) => {
        if (error.response && error.response.status === 401) {
          this.setState({
            data: null,
          });
        }
      });
  };

  // refresh user data every minutes
  // @todo get this interval from backend
  refreshData = () => {
    clearTimeout(this.refreshTimeout);
    this.refreshTimeout = setTimeout(() => {
      this.getData();
    }, 60000);
  };

  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled,
    });
  };

  // copy for autologin link
  onCopy = () => {
    this.autologinNode.select();
    window.document.execCommand('copy');
    this.setState({
      copied: true,
    });
  };

  handleClick = (e) => {
    if (!this.profile || this.profile.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false,
    });
  };

  render() {
    const { data, toggled, copied } = this.state;

    if (!data) {
      return null;
    }

    // check if edit profile page (My Account) is allowed
    const { allowedPages } = this.props;
    const allowEditProfile = allowedPages.includes(EDIT_PROFILE_TOPOLOGY_PAGE);

    const { fullname, username, autologinkey } = data;

    // creating autologin link, getting href, testing if there is a parameter, then generating link : if '?' then &autologin(etc.)
    const gethref = window.location.href;
    const conditionnedhref = gethref + (window.location.search ? '&' : '?');
    const autolink = `${conditionnedhref}autologin=1&useralias=${username}&token=${autologinkey}`;

    return (
      <div
        className={classnames(styles['wrap-right-user'], {
          [styles['submenu-active']]: toggled,
        })}
      >
        <Clock />
        <div ref={(profile) => (this.profile = profile)}>
          <span
            className={classnames(styles.iconmoon, styles['icon-user'])}
            onClick={this.toggle}
          />
          <div className={classnames(styles.submenu, styles.profile)}>
            <div className={styles['submenu-inner']}>
              <ul
                className={classnames(
                  styles['submenu-items'],
                  styles['list-unstyled'],
                )}
              >
                <li className={styles['submenu-item']}>
                  <span className={styles['submenu-item-link']}>
                    <span className={styles['submenu-user-name']}>
                      {fullname}
                    </span>
                    <span className={styles['submenu-user-type']}>
                      <Translate value="as" />
                      {` ${username}`}
                    </span>
                    {allowEditProfile && (
                      <Link
                        to={`/main.php?p=${EDIT_PROFILE_TOPOLOGY_PAGE}&o=c`}
                        className={styles['submenu-user-edit']}
                        onClick={this.toggle}
                      >
                        <Translate value="Edit profile" />
                      </Link>
                    )}
                  </span>
                </li>
                {autologinkey && (
                  <>
                    <button
                      className={styles['submenu-user-button']}
                      onClick={this.onCopy}
                    >
                      <Translate value="Copy autologin link" />
                      <span
                        className={classnames(
                          styles['btn-logout-icon'],
                          styles[copied ? 'icon-copied' : 'icon-copy'],
                        )}
                      />
                    </button>
                    <textarea
                      id="autologin-input"
                      className={styles['hidden-input']}
                      ref={(node) => (this.autologinNode = node)}
                      value={autolink}
                    />
                  </>
                )}
              </ul>
              <div className={styles['button-wrap']}>
                <a href="index.php?disconnect=1">
                  <button
                    className={classnames(
                      styles.btn,
                      styles['btn-small'],
                      styles.logout,
                    )}
                  >
                    <Translate value="Logout" />
                  </button>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

const mapStateToProps = (state) => ({
  allowedPages: allowedPagesSelector(state),
});

const mapDispatchToProps = {};

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(UserMenu);
