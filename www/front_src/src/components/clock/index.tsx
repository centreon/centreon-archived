/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-param-reassign */
/* eslint-disable import/no-extraneous-dependencies */

import React, { Component } from 'react';
import Moment from 'moment';
import 'moment-timezone/builds/moment-timezone-with-data-10-year-range'; // minimize bundle size (905KB -> 33KB)
import axios from '../../axios';

import styles from '../header/header.scss';

interface Data {
  date: string;
  time: string;
}

interface State {
  data: Data
}

const instantiateDate = (tz: string, locale: string, timestamp: number) => {
  const currentTime =
    tz !== '' ? Moment.unix(timestamp).tz(tz) : Moment.unix(timestamp);
  locale = locale !== null ? currentTime.locale(locale) : currentTime;

  return {
    date: currentTime.format('LL'),
    time: currentTime.format('LT'),
  };
};

class Clock extends Component<State> {
  private clockService = axios('internal.php?object=centreon_topcounter&action=clock');

  private refreshTimeout = null;

  public state = {
    data: null,
  };

  public componentDidMount() {
    this.getData();
  }

  public componentWillUnmount() {
    clearTimeout(this.refreshTimeout);
  }

  // fetch api to get clock data
  private getData = () => {
    this.clockService.get().then(({ data }) => {
      this.setState(
        {
          data: instantiateDate(data.timezone, data.locale, data.time),
        },
        this.refreshData,
      );
    });
  };

  // refresh clock data every 30 seconds
  // @todo get this interval from backend
  private refreshData = () => {
    clearTimeout(this.refreshTimeout);
    this.refreshTimeout = setTimeout(() => {
      this.getData();
    }, 30000);
  };

  public render() {
    const { data } = this.state;

    if (!data) {
      return null;
    }

    return (
      <div className={styles['wrap-right-timestamp']}>
        <span className={styles['wrap-right-date']}>{data.date}</span>
        <span className={styles['wrap-right-time']}>{data.time}</span>
      </div>
    );
  }
}

export default Clock;
