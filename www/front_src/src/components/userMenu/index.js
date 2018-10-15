import React, { Component } from "react";
import Clock from "../clock";
import config from "../../config";
import axios from "../../axios";

class UserMenu extends Component {

  userService = axios("internal.php?object=centreon_topcounter&action=user");

  refreshTimeout = null;

  state = {
    toggled: false,
    copied: false,
    data: null
  };

  UNSAFE_componentWillMount() {
    window.addEventListener('mousedown', this.handleClick, false);
    this.getData();
  };

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
    clearTimeout(this.refreshTimeout);
  };

  // fetch api to get user data
  getData = () => {
    this.userService.get().then(({data}) => {
      this.setState({
        data
      }, this.refreshData);
    }).catch((error) => {
      if (error.response.status == 401){
        this.setState({
          data: null
        });
      }
    });
  }

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
      toggled: !toggled
    });
  };

  //copy for autologin link
  onCopy = () => {
    this.autologinNode.select();
    window.document.execCommand('copy');
    this.setState({
      copied: true,
      toggled: false
    });
  };

  handleClick = (e) => {
    if (!this.profile || this.profile.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false
    });
  };

  render() {
    const { data, toggled, copied } = this.state;

    if (!data) {
      return null;
    }
    const { fullname, username, autologinkey } = data;

    //creating autologin link, getting href, testing if there is a parameter, then generating link : if '?' then &autologin(etc.)
    const gethref = window.location.href,
          conditionnedhref = gethref + (window.location.search ? '&' : '?'),
          autolink = conditionnedhref + 'autologin=1&useralias=' + username + '&token=' + autologinkey

    return (
      <div class={"wrap-right-user" + (toggled ? " submenu-active" : "")}>
        <Clock/>
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
                      value={autolink}
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