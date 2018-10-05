import React, { Component } from "react";
import Clock from "../clock";
import config from "../../config";

import axios from "../../axios";
import {generatePassword} from "../../helpers/autoLoginTokenGenerator";
class UserMenu extends Component {
  state = {
    toggled: false,
    copied: false
  };

  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled
    });
  };

  onCopy = () => {
    this.autologinNode.select();
    window.document.execCommand('copy');
    this.setState({
      copied: true,
      toggled: false
    });
  };

  UNSAFE_componentWillMount() {
    window.addEventListener('mousedown', this.handleClick, false);
  };

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
  };

  handleClick = (e) => {
    if (this.profile.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false
    });
  };

  getAutologinLink = () => {
    const { username, autologinkey } = this.props.data

    return window.location.href + '?autologin=1&useralias=' + username + '&token=' + autologinkey
  };

  render() {
    const { data, clockData } = this.props;

    if (!data) {
      return null;
    }

    const { toggled, copied } = this.state,
          { fullname, username, autologinkey } = data;

    return (
      <div class={"wrap-right-user" + (toggled ? " submenu-active" : "")}>
        <Clock clockData={clockData} />
        <div ref={profile => this.profile = profile}>
          <span class="iconmoon icon-user" onClick={this.toggle} />
          <div class={"submenu profile"}>
            <div class="submenu-inner">
              <ul class="submenu-items list-unstyled">
                <li class="submenu-item">
                  <span class="submenu-item-link">
                    <span class="submenu-user-name">{fullname}</span>
                    <span class="submenu-user-type">as {username}</span>
                    <a class="submenu-user-edit" href={config.urlBase + "main.php?p=50104&o=c"}>
                      Edit profile
                    </a>
                  </span>
                </li>
                {autologinkey &&
                  <React.Fragment>
                    <button
                      className={'submenu-user-button'}
                      onClick={this.onCopy}
                    >
                      Copy autologin link
                      <span className={"btn-logout-icon icon-copy " + (copied && ["icon-copied"])}></span>
                    </button>
                    <textarea
                      id="autologin-input"
                      className={'hidden-input'}
                      ref={node => this.autologinNode = node}
                      value={window.location.href + '?autologin=1&useralias=' + username + '&token=' + autologinkey}
                    />
                  </React.Fragment>
                }
              </ul>
              <div class="button-wrap">
                <a href={config.urlBase + "index.php?disconnect=1"}>
                  <button class="btn btn-small logout">Log out</button>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default UserMenu;