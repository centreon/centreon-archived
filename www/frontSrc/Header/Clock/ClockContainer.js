import React, { Component } from 'react'
import { connect } from 'react-redux'
import Clock from './Clock'
import { getClock } from "../../webservices/clockApi"
import 'moment-timezone'
import Moment from 'moment'

class ClockContainer extends Component {

  constructor(props) {
    super(props)
    this.state = {
      currentDate: {},
    }
  }

  componentWillReceiveProps(nextProps) {
    if (this.props !== nextProps) {
      this.setState({
        currentDate: this.setDate(nextProps.clock.timezone, nextProps.clock.locale, nextProps.clock.time)
      })
    }
  }

  componentDidMount = () =>  {
    this.props.getClock()

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
    const { currentDate } = this.state

    return (
      <Clock
        currentDate={currentDate}
      />
    )
  }
}

const mapStateToProps = (store) => {
  return {
    clock: store.clock.data,
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    getClock: () => {
      return dispatch(getClock())
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(ClockContainer)