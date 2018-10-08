import React, { Component } from "react";
import PropTypes from 'prop-types';
import numeral from "numeral";
import { Link } from "react-router-dom";
import config from "../../config";

class ServiceStatusMenu extends Component {

  state = {
    toggled: false
  };

  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled
    });

  };

  UNSAFE_componentWillMount() {
    window.addEventListener('mousedown', this.handleClick, false);
  };

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
  };

  handleClick = (e) => {
    if (this.service.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false
    });
  };

  render() {
    let data = this.props.data;
    
    if(!data || !data.total){
      data = {
        warning: {
          total: null,
          unhandled: null
        },
        critical: {
          total: null,
          unhandled: null
        },
        unknown: {
          total: null,
          unhandled: null
        },
        ok: null,
        pending: null,
        total: 0,
      }
    }

    let { critical, ok, pending, total, unknown, warning } = data;

    const { toggled } = this.state;

    return (
      <div  class={"wrap-right-services" + (toggled ? " submenu-active" : "")}>
        <span class="wrap-right-icon" onClick={this.toggle.bind(this)}>
          <span class="iconmoon icon-services">
            {pending > 0 ? <span class="custom-icon" /> : null}
          </span>
          <span class="wrap-right-icon__name">Services</span>
        </span>
        <Link to={config.urlBase + "main.php?p=20201&o=svc_critical&search="} class={"wrap-middle-icon round round-small " + (critical.unhandled > 0 ? "red" : "red-bordered")} >
          <span class="number">
            <span id="count-svc-critical">{numeral(critical.unhandled).format("0a")}</span>
          </span>
        </Link>
        <Link to={config.urlBase + "main.php?p=20201&o=svc_warning&search="} class={"wrap-middle-icon round round-small " + (warning.unhandled > 0 ? "orange" : "orange-bordered")}>
          <span class="number">
            <span id="count-svc-warning">{numeral(warning.unhandled).format("0a")}</span>
          </span>
        </Link>
        <Link to={config.urlBase + "main.php?p=20201&o=svc_unknown&search="} class={"wrap-middle-icon round round-small " + (unknown.unhandled > 0 ? "gray-light" : "gray-light-bordered")}>
          <span class="number">
            <span id="count-svc-unknown">{numeral(unknown.unhandled).format("0a")}</span>
          </span>
        </Link>
        <Link to={config.urlBase + "main.php?p=20201&o=svc_ok&search="} class={"wrap-middle-icon round round-small " + (ok > 0 ? "green" : "green-bordered")}>
          <span class="number">
            <span id="count-svc-ok">{numeral(ok).format("0a")}</span>
          </span>
        </Link>
        <div ref={service => this.service = service}>
          <span ref={this.setWrapperRef} class="toggle-submenu-arrow" onClick={this.toggle.bind(this)} >{this.props.children}</span>
          <div class="submenu services">
            <div class="submenu-inner">
              <ul class="submenu-items list-unstyled">
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20201&o=svc&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span>All services:</span>
                      <span class="submenu-count">{total}</span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20201&o=svc_critical&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored red">Critical services:</span>
                      <span class="submenu-count">
                      {critical.unhandled}/{critical.total}
                      </span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20201&o=svc_warning&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored orange">Warning services:</span>
                      <span class="submenu-count">
                        {warning.unhandled}/{warning.total}
                      </span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20201&o=svc_unknown&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored gray-light">Unknown services:</span>
                      <span class="submenu-count">
                        {unknown.unhandled}/{unknown.total}
                      </span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20201&o=svc_ok&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored green">Ok services:</span>
                      <span class="submenu-count">{ok}</span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20201&o=svc_pending&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored blue">Pending services:</span>
                      <span class="submenu-count">{pending}</span>
                    </div>
                  </Link>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    );
  }
}



export default ServiceStatusMenu;

ServiceStatusMenu.propTypes = {
  children: PropTypes.element.isRequired,
};

