import React, { Component } from "react";
import "moment-timezone";
import Moment from "moment";
import axios from "../../axios";

const instantiateDate = (tz, locale, timestamp) => {
  const currentTime = tz !== "" ? Moment.unix(timestamp).tz(tz) : Moment.unix(timestamp);
  locale = locale !== null ? currentTime.locale(locale) : currentTime;

  return {
    date: currentTime.format("LL"),
    time: currentTime.format("LT")
  };
};

class Clock extends Component {

  clockService = axios("internal.php?object=centreon_topcounter&action=clock");

  refreshTimeout = null;

  state = {
    data: null
  };

  UNSAFE_componentWillMount() {
    this.getData();
  };

  componentWillUnmount() {
    clearTimeout(this.refreshTimeout);
  };

  // fetch api to get clock data
  getData = () => {
    this.clockService.get().then(({data}) => {
      this.setState({
        data: instantiateDate(data.timezone, data.locale, data.time)
      }, this.refreshData);
    });
  }

  // refresh clock data every 30 seconds
  // @todo get this interval from backend
  refreshData = () => {
    clearTimeout(this.refreshTimeout);
    this.refreshTimeout = setTimeout(() => {
      this.getData();
    }, 30000);
  };


  render() {
    const { data } = this.state;

    if (!data) {
      return null;
    }

    return (
      <div class="wrap-right-timestamp">
        <span class="wrap-right-date">{data.date}</span>
        <span class="wrap-right-time">{data.time}</span>
      </div>
    );
  }
}

export default Clock;
