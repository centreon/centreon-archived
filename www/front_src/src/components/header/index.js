import React, {Component} from "react";
import {connect} from "react-redux";

import PollerMenu from "../pollerMenu";
import UserMenu from "../userMenu";
import HostMenu from "../hostMenu";
import ServiceStatusMenu from "../serviceStatusMenu";

class TopHeader extends Component {

  render() {
    return (
      <header class="header">
        <div class="header-icons">
          <div class="wrap wrap-left">
            <PollerMenu/>
          </div>
          <div class="wrap wrap-right">
            <HostMenu/>
            <ServiceStatusMenu/>
            <UserMenu/>
          </div>
        </div>
      </header>
    );
  };
}

export default connect(null, null)(TopHeader);
