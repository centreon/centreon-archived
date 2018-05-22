import React, { Component } from 'react'
import PollerObject from './PollerObject'
import {connect} from "react-redux"
import {getPollers} from "../../webservices/pollerApi"

class PollerObjectContainer extends Component {

  constructor(props) {
    super(props)
    this.state = {
      anchorEl: null,
    }
  }

  componentDidMount() {
    this.props.getPollers()
  }

  componentWillUnmount() {
    clearTimeout(this.timeout);
  }

  componentWillReceiveProps(nextProps) {
    if (this.props.poller !== nextProps.poller) {
      clearTimeout(this.timeout);

      if (!nextProps.poller.isFetching) {
        this.refresh();
      }
    }
  }

  refresh = () => {
    this.timeout = setTimeout(() => this.props.getPollers(), this.props.poller.refreshTime)
  }

  handleOpen = event => {
    this.setState({ anchorEl: event.currentTarget })
  }

  handleClose = () => {
    this.setState({ anchorEl: null })
  }

  setPollerState = (database, latency, stability) => {
    const pollerState = {
      color: '#88B917',
    }

    if (database.critical > 0 || latency.critical > 0 || stability.critical > 0) {
      pollerState.color = '#E00B3D'
    } else if (database.warning > 0 || latency.warning > 0 || stability.warning > 0) {
      pollerState.color = '#FF9A13'
    }

    return pollerState
  }

  render () {
    const { anchorEl } = this.state
    const open = !!anchorEl
    const { database, latency, stability, total, dataFetched, error } = this.props.poller

    if (dataFetched) {
      const {color} = this.setPollerState(stability, database, latency)
      return (
        <PollerObject
          handleClose={this.handleClose}
          handleOpen={this.handleOpen}
          open={open}
          anchorEl={anchorEl}
          iconColor={color ? color : '#BCBDC0'}
          database={database ? database : {critical: '...', warning: '...'}}
          latency={latency ? latency : {critical: '...', warning: '...'}}
          stability={stability ? stability : {critical: '...', warning: '...'}}
          total={total ? total : '...'}
        />
      )
    } else {
      if (error === false && error != null) {
        return (
          <PollerObject
            open={false}
            id='pollerIcon'
            alt="poller icon"
            nativeColor='#A7A9AC'
            style={{cursor: 'none'}}
            iconColor='#A7A9AC'
            database={{critical: '...', warning: '...'}}
            latency={{critical: '...', warning: '...'}}
            stability={{critical: '...', warning: '...'}}
            total='...'
          />
        )
      } else {
        return null
      }
    }
  }
}

const mapStateToProps = (store) => {
  return {
    poller: store.poller,
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    getPollers: () => {
      return dispatch(getPollers())
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(PollerObjectContainer)