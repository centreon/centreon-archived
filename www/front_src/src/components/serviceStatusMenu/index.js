import React, { Component } from "react";
import PropTypes from 'prop-types';
import numeral from "numeral";
import { Link } from "react-router-dom";
import config from "../../config";
import {Translate} from 'react-redux-i18n';
import axios from "../../axios";

import { connect } from "react-redux";

class ServiceStatusMenu extends Component {

  servicesStatusService = axios(
    "internal.php?object=centreon_topcounter&action=servicesStatus"
  );

  refreshInterval = null;

  state = {
    toggled: false,
    data: null,
    intervalApplied: false
  };

  UNSAFE_componentWillMount() {
    window.addEventListener('mousedown', this.handleClick, false);
  };

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
    clearInterval(this.refreshInterval);
  };

  // fetch api to get service data
  getData = () => {
    this.servicesStatusService.get().then(({data}) => {
      this.setState({
        data
      });
    }).catch((error) => {
      if (error.response.status == 401){
        this.setState({
          data: null
        });
      }
    });
  }

  componentWillReceiveProps = (nextProps) => {
    const {refreshTime} = nextProps;
    const {intervalApplied} = this.state;
    if(refreshTime && !intervalApplied){
      this.getData();
      this.refreshInterval = setInterval(() => {
        this.getData();
      }, refreshTime);
      this.setState({
        intervalApplied:true
      })
    }
  }

  // display/hide detailed service data
  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled
    });
  };

  // hide service detailed data if click outside
  handleClick = (e) => {
    if (!this.service || this.service.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false
    });
  };

  render() {
    const { data, toggled } = this.state;

    // do not display service information until having data
    if (!data) {
      return null
    }

    return (
      <div  class={"wrap-right-services" + (toggled ? " submenu-active" : "")}>
        <span class="wrap-right-icon" onClick={this.toggle.bind(this)}>
          <span class="iconmoon icon-services">
            {data.pending > 0 ? <span class="custom-icon" /> : null}
          </span>
          <span class="wrap-right-icon__name">Services</span>
        </span>
        <Link to={config.urlBase + "main.php?p=20201&o=svc_critical&search="} class={"wrap-middle-icon round round-small " + (data.critical.unhandled > 0 ? "red" : "red-bordered")} >
          <span class="number">
            <span id="count-svc-critical">{numeral(data.critical.unhandled).format("0a")}</span>
          </span>
        </Link>
        <Link to={config.urlBase + "main.php?p=20201&o=svc_warning&search="} class={"wrap-middle-icon round round-small " + (data.warning.unhandled > 0 ? "orange" : "orange-bordered")}>
          <span class="number">
            <span id="count-svc-warning">{numeral(data.warning.unhandled).format("0a")}</span>
          </span>
        </Link>
        <Link to={config.urlBase + "main.php?p=20201&o=svc_unknown&search="} class={"wrap-middle-icon round round-small " + (data.unknown.unhandled > 0 ? "gray-light" : "gray-light-bordered")}>
          <span class="number">
            <span id="count-svc-unknown">{numeral(data.unknown.unhandled).format("0a")}</span>
          </span>
        </Link>
        <Link to={config.urlBase + "main.php?p=20201&o=svc_ok&search="} class={"wrap-middle-icon round round-small " + (data.ok > 0 ? "green" : "green-bordered")}>
          <span class="number">
            <span id="count-svc-ok">{numeral(data.ok).format("0a")}</span>
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
                      <span><Translate value="All Services"/>:</span>
                      <span class="submenu-count">{data.total}</span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20201&o=svc_critical&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored red"><Translate value="Critical services"/>:</span>
                      <span class="submenu-count">
                      {data.critical.unhandled}/{data.critical.total}
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
                      <span class="dot-colored orange"><Translate value="Warning services"/>:</span>
                      <span class="submenu-count">
                        {data.warning.unhandled}/{data.warning.total}
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
                      <span class="dot-colored gray-light"><Translate value="Unknown services"/>:</span>
                      <span class="submenu-count">
                        {data.unknown.unhandled}/{data.unknown.total}
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
                      <span class="dot-colored green"><Translate value="Ok services"/>:</span>
                      <span class="submenu-count">{data.ok}</span>
                    </div>
                  </Link>
                </li>
                <li class="submenu-item">
                  <Link
                    to={config.urlBase + "main.php?p=20201&o=svc_pending&search="}
                    class="submenu-item-link"
                  >
                    <div onClick={this.toggle}>
                      <span class="dot-colored blue"><Translate value="Pending services"/>:</span>
                      <span class="submenu-count">{data.pending}</span>
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


const mapStateToProps = ({ navigation, intervals }) => ({
  navigationData: navigation,
  refreshTime: intervals ? parseInt(intervals.AjaxTimeReloadMonitoring)*1000 : false
});

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(ServiceStatusMenu);

ServiceStatusMenu.propTypes = {
  children: PropTypes.element.isRequired,
};

