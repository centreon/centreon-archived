import React, { Component } from "react";
import numeral from "numeral";
import {Link} from 'react-router-dom';
import PropTypes from 'prop-types';
import config from "../../config";
import axios from "../../axios";

class HostMenu extends Component {

  hostsService = axios(
    "internal.php?object=centreon_topcounter&action=hosts_status"
  );

  refreshTimeout = null;

  state = {
    toggled: false,
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

  // fetch api to get host data
  getData = () => {
    this.hostsService.get().then(({data}) => {
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

  // refresh host data every 15 seconds
  // @todo get this interval from backend
  refreshData = () => {
    clearTimeout(this.refreshTimeout);
    this.refreshTimeout = setTimeout(() => {
      this.getData();
    }, 15000);
  };

  // display/hide detailed host data
  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled
    });
  };

  // hide host detailed data if click outside
  handleClick = (e) => {
    if (!this.host || this.host.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false
    });
  };

  render() {
    const { data, toggled } = this.state;

    // do not display host information until having data
    if (!data) {
      return null
    }

    return (
      <div class={"wrap-right-hosts" + (toggled ? " submenu-active" : "")}>
        <span class="wrap-right-icon" onClick={this.toggle.bind(this)}>
          <span class="iconmoon icon-hosts">
            {data.pending > 0 ? <span class="custom-icon" /> : null}
          </span>
          <span class="wrap-right-icon__name">Hosts</span>
        </span>

        <Link to={config.urlBase + "main.php?p=20202&o=h_down&search="} class={"wrap-middle-icon round round-small "+ (data.down.unhandled > 0 ? "red" : "red-bordered")}>
          <span class="number">
            <span id="count-host-down">{numeral(data.down.unhandled).format("0a")}</span>
          </span>
        </Link>
        <Link to={config.urlBase + "main.php?p=20202&o=h_unreachable&search="} class={"wrap-middle-icon round round-small "+ (data.unreachable.unhandled > 0 ? "gray-dark" : "gray-dark-bordered")}>
          <span class="number">
            <span id="count-host-unreachable">{numeral(data.unreachable.unhandled).format("0a")}</span>
          </span>
        </Link>
        <Link to={config.urlBase + "main.php?p=20202&o=h_up&search="} class={"wrap-middle-icon round round-small "+ (data.ok > 0 ? "green" : "green-bordered")}>
          <span class="number">
            <span id="count-host-up">{numeral(data.ok).format("0a")}</span>
          </span>
        </Link>
        <div ref={host => this.host = host}>
          <span class="toggle-submenu-arrow" onClick={this.toggle.bind(this)} >{this.props.children}</span>
          <div class="submenu host">
            <div class="submenu-inner">
              <ul class="submenu-items list-unstyled">
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20202&o=h&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span>All</span>
                      <span class="submenu-count">{numeral(data.total).format("0a")}</span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20202&o=h_down&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored red">Down</span>
                      <span class="submenu-count">
                        {numeral(data.down.unhandled).format("0a")}/{numeral(data.down.total).format("0a")}
                      </span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20202&o=h_unreachable&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored gray">Unreachable</span>
                      <span class="submenu-count">
                        {numeral(data.unreachable.unhandled).format("0a")}/{numeral(data.unreachable.total).format("0a")}
                      </span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20202&o=h_up&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored green">Up</span>
                      <span class="submenu-count">{numeral(data.ok).format("0a")}</span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20202&o=h_pending&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored blue">Pending</span>
                      <span class="submenu-count">{numeral(data.pending).format("0a")}</span>
                    </div>
                  </Link>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    )
  }
}

export default HostMenu;
HostMenu.propTypes = {
  children: PropTypes.element.isRequired,
};
