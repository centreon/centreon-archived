import React, { Component } from 'react'
import { connect } from 'react-redux'
import ClockComponent from './Clock'
import { getClock } from "../../webservices/clockApi"
import { timeDispatcher } from "../Actions/clockActions"
import 'moment-timezone'
import Moment from 'moment'

class ClockContainer extends Component {

  constructor(props) {
    super(props)
    this.state = {
      currentDate: null,
    }
  }

  componentWillReceiveProps(nextProps) {
    const { timezone, locale } = this.props.clock
    if (timezone !== nextProps.clock.timezone || locale !== nextProps.clock.locale) {
      clearTimeout(this.timeout)
      if (!nextProps.clock.isFetching) {
        this.refresh()
      }
    }
  }

  componentDidMount = () =>  {
    this.props.getClock()
    this.refreshClock()
  }

  componentWillUnmount() {
    clearTimeout(this.timeout)
    clearInterval(this.interval)
  }

  refreshClock = () => {
    this.interval = setInterval(() => this.props.timeDispatcher(this.props.clock.date, this.props.clock.timezone, this.props.clock.locale), 1000)
  }

  refresh = () => {
    this.timeout = setTimeout(() => this.props.getClock(), this.props.clock.refreshTime)
  }

  setDate = (tz, locale, timestamp) => {

    const currentTime = tz !== '' ? Moment.unix(timestamp).tz(tz) : Moment.unix(timestamp)
    locale !== null ? currentTime.locale(locale) : currentTime

    return {
      date: currentTime.format('LL'),
      time: currentTime.format('LT')
    }
  }

  render () {
    const { dataFetched, date, timezone, locale } = this.props.clock

    if(dataFetched) {
      const currentTime = this.setDate(timezone, locale, date)
      return <ClockComponent currentTime={currentTime} />
    } else return null
  }
}

const mapStateToProps = (store) => {
  return {
    clock: store.clock,
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    getClock: () => {
      return dispatch(getClock())
    },
    timeDispatcher: (time, timezone, locale) => {
      return dispatch(timeDispatcher(time, timezone, locale))
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(ClockContainer)