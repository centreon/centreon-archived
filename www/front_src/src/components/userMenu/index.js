import React, { Component } from "react";
import numeral from "numeral";
import Clock from "../clock";

class UserMenu extends Component {
  state = {
    toggled: false
  };

  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled
    });
  };

  render() {
    const { data, clockData } = this.props;

    if (!data) {
      return null;
    }

    const { toggled } = this.state;

    const { fullname, username } = data;

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
              <button class="submenu-user-button">Copy autologin link <span class="btn-logout-icon"></span></button>
              <span class="iconmoon icon-copy" />
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
