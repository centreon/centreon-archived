import React, { Component } from "react";
import numeral from "numeral";
import {Link} from 'react-router-dom';
import PropTypes from 'prop-types';

class HostMenu extends Component {

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
    let data = this.props.data;

    if(!data || !data.total){
      data = {
        down: {
          total: null,
          unhandled: null
        },
        unreachable: {
          total: null,
          unhandled: null
        },
        ok: null,
        pending: null,
        total: 0,
      }
    }

    let { down, unreachable, ok, pending, total } = data;

    

    const { toggled } = this.state;

    return (
      <div class={"wrap-right-hosts" + (toggled ? " submenu-active" : "")}>
        <span class="wrap-right-icon" onClick={this.toggle.bind(this)}>
          <span class="iconmoon icon-hosts">
            {pending > 0 ? <span class="custom-icon" /> : null}
          </span>
          <span class="wrap-right-icon__name">Hosts</span>
        </span>

        <Link to="./main.php?p=20202&o=h_down&search=" class={"wrap-middle-icon round round-small "+ (down.unhandled > 0 ? "red" : "red-bordered")}>
          <a class="number">
            <span>{numeral(down.unhandled).format("0a")}</span>
          </a>
        </Link>
        <Link to="./main.php?p=20202&o=h_unreachable&search=" class={"wrap-middle-icon round round-small "+ (unreachable.unhandled > 0 ? "gray-dark" : "gray-dark-bordered")}>
          <a class="number">
            <span>{numeral(unreachable.unhandled).format("0a")}</span>
          </a>
        </Link>
        <Link to="./main.php?p=20202&o=h_up&search=" class={"wrap-middle-icon round round-small "+ (ok > 0 ? "green" : "green-bordered")}>
          <a class="number">
            <span>{numeral(ok).format("0a")}</span>
          </a>
        </Link>

        <span class="toggle-submenu-arrow" onClick={this.toggle.bind(this)} >{this.props.children}</span>
        <div class="submenu host">
          <div class="submenu-inner">
            <ul class="submenu-items list-unstyled">
              <li class="submenu-item">
                <Link
                  to={"./main.php?p=20202&o=h&search="}
                  class="submenu-item-link"
                >
                  <span>All</span>
                  <span class="submenu-count">{numeral(total).format("0a")}</span>
                </Link>
              </li>
              <li class="submenu-item">
                <Link
                  to={"./main.php?p=20202&o=h_down&search="}
                  class="submenu-item-link"
                >
                  <span class="dot-colored red">Down</span>
                  <span class="submenu-count">
                    {numeral(down.unhandled).format("0a")}/{numeral(down.total).format("0a")}
                  </span>
                </Link>
              </li>
              <li class="submenu-item">
                <Link
                  to={"./main.php?p=20202&o=h_unreachable&search="}
                  class="submenu-item-link"
                >
                  <span class="dot-colored gray">Unreachable</span>
                  <span class="submenu-count">
                    {numeral(unreachable.unhandled).format("0a")}/{numeral(unreachable.total).format("0a")}
                  </span>
                </Link>
              </li>
              <li class="submenu-item">
                <Link
                  to={"./main.php?p=20202&o=h_up&search="}
                  class="submenu-item-link"
                >
                  <span class="dot-colored green">Ok</span>
                  <span class="submenu-count">{numeral(ok).format("0a")}</span>
                </Link>
              </li>
              <li class="submenu-item">
                <Link
                  to={"./main.php?p=20202&o=h_pending&search="}
                  class="submenu-item-link"
                >
                  <span class="dot-colored blue">Pending</span>
                  <span class="submenu-count">{numeral(pending).format("0a")}</span>
                </Link>
              </li>
            </ul>
          </div>
        </div>
      </div>
    );
  }
}

export default HostMenu;
HostMenu.propTypes = {
  children: PropTypes.element.isRequired,
};
