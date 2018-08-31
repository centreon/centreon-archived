import React, { Component } from "react";
import numeral from "numeral";

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
    const { data } = this.props;

    if (!data) {
      return null;
    }

    const { toggled } = this.state;

    const { fullname, username } = data;

    return (
      <div class="wrap-right-icon round round-small white">
        <span class="icon-profile profile" onClick={this.toggle.bind(this)}>
          D
        </span>
        <div
          class={"submenu-top profile" + (toggled ? " submenu-active" : null)}
        >
          <div class="submenu-top-inner">
            <ul class="submenu-top-items">
              <li class="submenu-top-item">
                <span class="submenu-top-item-link">
                  <span>{fullname}</span>
                  <span class="submenu-top-user-type">as {username}</span>
                  <a
                    class="submenu-top-user-edit"
                    href="./main.php?p=50104&o=c"
                  >
                    Edit profile
                  </a>
                </span>
              </li>
            </ul>
            <a href="index.php?disconnect=1">
              <button class="btn btn-small btn-red" type="button">
                Sign out
              </button>
            </a>
          </div>
        </div>
      </div>
    );
  }
}

export default UserMenu;
