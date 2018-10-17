import React, {Component} from "react";
import {connect} from "react-redux";

import PollerMenu from "../pollerMenu";
import UserMenu from "../userMenu";
import HostMenu from "../hostMenu";
import ServiceStatusMenu from "../serviceStatusMenu";

class TopHeader extends Component {

  render() {
    const {refreshIntervals} = this.props;
    return (
      <header class="header">
        <div class="header-icons">
          <div class="wrap wrap-left">
            <PollerMenu refreshIntervals={refreshIntervals}/>
          </div>
          <div class="wrap wrap-right">
            <HostMenu refreshIntervals={refreshIntervals}/>
            <ServiceStatusMenu refreshIntervals={refreshIntervals}/>
            <UserMenu refreshIntervals={refreshIntervals}/>
          </div>
        </div>
      </header>
    );
  };
}

export default connect(null, null)(TopHeader);
