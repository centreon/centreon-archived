import React, { Component } from "react";
import numeral from "numeral";
import {CopyToClipboard}  from 'react-copy-to-clipboard';
import Clock from "../clock";

class UserMenu extends Component {
  state = {
    toggled: false,
    value: 'token=f855810b7eafb9cfb0c3d74c62af0fb2e2647939', 
    copied: false
  };


  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled
    });
  };


  onCopy = () => {
    console.log('click on copy');
    this.setState({copied: true});
  };

  render() {
    const { data, clockData } = this.props;

    if (!data) {
      return null;
    }

    const { toggled } = this.state,
          { fullname, username } = data;

    return (
      <div class={"wrap-right-user" + (toggled ? " submenu-active" : "")}>
        <Clock clockData={clockData} />
        <span class="iconmoon icon-user" onClick={this.toggle.bind(this)} />
        <div class={"submenu profile"}>
          <div class="submenu-inner">
            <ul class="submenu-items list-unstyled">
              <li class="submenu-item">
                <span class="submenu-item-link">
                  <span class="submenu-user-name">{fullname}</span>
                  <span class="submenu-user-type">as {username}</span>
                  <a class="submenu-user-edit" href="./main.php?p=50104&o=c">
                    Edit profile
                  </a>
                </span>
              </li>
              <CopyToClipboard onCopy={this.onCopy} text={toggled.value}>
                <React.Fragment>
                  <button class="submenu-user-button">Copy autologin link <span className={toggled.copied ?'icon-copied btn-logout-icon' : 'icon-copy btn-logout-icon' }></span></button>
                  <span  />
                </React.Fragment>

               </CopyToClipboard>
            </ul>
            <div class="button-wrap">
              <a href="index.php?disconnect=1">
                <button class="btn btn-small logout">Log out</button>
              </a>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default UserMenu;
