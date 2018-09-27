import React, { Component } from "react";
import numeral from "numeral";
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
    let autoLoginInput = document.getElementById("autologin-input");
    autoLoginInput.select();
    document.execCommand('copy');
    this.setState({ copied: true });
  };

  render() {
    const { data, clockData } = this.props;

    if (!data) {
      return null;
    }

    const { toggled, copied, value } = this.state,
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
              <React.Fragment>
                <button class="submenu-user-button" onClick={this.onCopy.bind(this)}>Copy autologin link <span className={`btn-logout-icon ${(copied ? 'icon-copied' : 'icon-copy')}`}></span></button>
                <textarea id="autologin-input" style={
                  {
                    width: 0,
                    height: 0,
                    position: 'fixed',
                    top: -100
                  }
                }>{value}</textarea>
              </React.Fragment>

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
