import React, { Component } from "react";

import routeMap from "../../route-maps";
import { Link } from "react-router-dom";
import { connect } from "react-redux";

import "moment-timezone";
import Moment from "moment";

import Clock from "../clock";

import axios from "../../axios";
import PollerMenu from "../pollerMenu";
import UserMenu from "../userMenu";
import HostMenu from "../hostMenu";
import ServiceStatusMenu from "../serviceStatusMenu";

const instantiateDate = (tz, locale, timestamp) => {
  const currentTime =
    tz !== "" ? Moment.unix(timestamp).tz(tz) : Moment.unix(timestamp);
  locale = locale !== null ? currentTime.locale(locale) : currentTime;

  return {
    date: currentTime.format("LL"),
    time: currentTime.format("LT")
  };
};

class TopHeader extends Component {
  pollerService = axios(
    "internal.php?object=centreon_topcounter&action=pollersListIssues"
  );
  hostsService = axios(
    "internal.php?object=centreon_topcounter&action=hosts_status"
  );
  servicesStatusService = axios(
    "internal.php?object=centreon_topcounter&action=servicesStatus"
  );
  userService = axios("internal.php?object=centreon_topcounter&action=user");
  clockService = axios("internal.php?object=centreon_topcounter&action=clock");

  state = {
    pollerData: {},
    hostsData: {},
    clockData: {},
    servicesStatusData: {},
    userData: {}
  };

  refreshInterval;

  setClock = () => {
    this.clockService.get().then(({ data }) => {
      this.setState({
        clockData: instantiateDate(data.timezone, data.locale, data.time)
      });
    });
  };

  setRefreshInterval = () => {
    this.refreshInterval = setInterval(this.setClock, 15000);
  };

  componentWillUnmount = () => {
    clearInterval(this.refreshInterval);
  };

  UNSAFE_componentWillMount = () => {
    this.pollerService.get().then(({ data }) => {
      this.setState({
        pollerData: data
      });
    });
    this.hostsService.get().then(({ data }) => {
      this.setState({
        hostsData: data
      });
    });
    this.servicesStatusService.get().then(({ data }) => {
      this.setState({
        servicesStatusData: data
      });
    });
    this.userService.get().then(({ data }) => {
      this.setState({
        userData: data
      });
    });
    this.setClock();
    this.setRefreshInterval();
  };

  render = () => {
    const {
      pollerData,
      clockData,
      hostsData,
      servicesStatusData,
      userData
    } = this.state;
    return (
      <header class="header">
        <div class="header-icons">
          <div class="wrap wrap-left">
            <PollerMenu data={pollerData} />
          </div>
          <div class="wrap wrap-right">
            <HostMenu data={hostsData} />
            <ServiceStatusMenu data={servicesStatusData} />
            <UserMenu data={userData} clockData={clockData} />
          </div>
        </div>
      </header>
    );
  };
}
export default connect(null, null)(TopHeader);
