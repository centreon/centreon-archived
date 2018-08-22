import React, { Component } from "react";

import routeMap from "../../route-maps";
import { Link } from "react-router-dom";
import { connect } from "react-redux";

import 'moment-timezone'
import Moment from 'moment'

import axios from "../../axios";

import logo from "../../img/centreon.png";
import IconMenu from '../iconMenu';
import LineMenu from "../lineMenu";
import PollerMenu from '../pollerMenu';
import Clock from '../clock';
import HostMenu from "../hostMenu";
import ServiceStatusMenu from '../serviceStatusMenu';
import UserMenu from "../userMenu";


const instantiateDate = (tz, locale, timestamp) => {
  const currentTime = tz !== '' ? Moment.unix(timestamp).tz(tz) : Moment.unix(timestamp)
  locale !== null ? currentTime.locale(locale) : currentTime

  return {
      date: currentTime.format('LL'),
      time: currentTime.format('LT')
  }
}

class TopHeader extends Component {

  navService = axios('internal.php?object=centreon_menu&action=menu');
  pollerService = axios('internal.php?object=centreon_topcounter&action=pollersListIssues');
  hostsService = axios('internal.php?object=centreon_topcounter&action=hosts_status');
  servicesStatusService = axios('internal.php?object=centreon_topcounter&action=servicesStatus');
  userService = axios('internal.php?object=centreon_topcounter&action=user');
  clockService = axios('internal.php?object=centreon_topcounter&action=clock');

  state = {
    selectedMenu: {},
    pollerData: {},
    hostsData: {},
    clockData:{},
    servicesStatusData: {},
    userData: {},
    menuItems: []
  };

  refreshInterval;

  transformToArray = (data, callback) => {
    let result = [];
    for (var key in data) {
      result.push(data[key]);
    }
    callback(result);
  }

  setClock = () => {
    this.clockService.get().then(({data}) => {
      this.setState({
        clockData:instantiateDate(data.timezone, data.locale, data.time)
      })
    })
  }

  setRefreshInterval = () => {
    this.refreshInterval = setInterval(this.setClock, 15000)
  }

  componentWillUnmount = () => {
    clearInterval(this.refreshInterval);
  }


  componentWillMount = () => {
    this.navService.get().then(({ data }) => {
      this.transformToArray(data, (array) => {
        this.setState({
          menuItems: array,
          selectedMenu: array[0]
        });
      })
    });
    this.pollerService.get().then(({ data }) => {
      this.setState({
        pollerData:data
      })
    })
    this.hostsService.get().then(({ data }) => {
      this.setState({
        hostsData:data
      })
    })
    this.servicesStatusService.get().then(({ data }) => {
      this.setState({
        servicesStatusData:data
      })
    })
    this.userService.get().then(({ data }) => {
      this.setState({
        userData:data
      })
    })
    this.setClock();
    this.setRefreshInterval();
  };

  switchTopLevelMenu = selectedMenu => {
    this.setState({
      selectedMenu
    });
  };

  render = () => {
    const { menuItems, selectedMenu, pollerData, clockData, hostsData, servicesStatusData,userData } = this.state;
    return (
      <div>
        <header class="header mb-3">
          <div class="header-wrapper">
            <div class="header-inner">
              <div class="header-top">
                <div class="header-top-logo">
                  <Link to={routeMap.home}>
                    <img src={logo} width="254" height="57" alt="" />
                  </Link>
                </div>
                <div class="header-top-icons">
                  <IconMenu
                    items={menuItems}
                    selected={selectedMenu}
                    onSwitch={this.switchTopLevelMenu.bind(this)} />
                  <div class="wrap-middle">
                    <div class="wrap-middle-left">
                      <PollerMenu data={pollerData}/>
                      <HostMenu data={hostsData}/>
                    </div>
                    <ServiceStatusMenu data={servicesStatusData}/>
                  </div>
                  <div class="wrap-right">
                    <Clock clockData={clockData}/>
                    <UserMenu data={userData}/>
                  </div>
                </div>
              </div>
              <LineMenu menu={selectedMenu} />
            </div>
          </div>
        </header>
      </div>
    );
  };
}
export default connect(null, null)(TopHeader);
