import React, { Component } from 'react'
import PollerObject from './PollerObject'
import {connect} from "react-redux"
import {getPollersStatus, getPollersListIssues} from "../../webservices/pollerApi"

class PollerObjectContainer extends Component {

  constructor(props) {
    super(props)
    this.state = {
      anchorEl: null,
    }
  }

  componentDidMount() {
    this.props.getPollersStatus()
    this.props.getPollersListIssues()
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

  setPollerState = (issues) => {

    let pollerState = '#88B917'
    const issuesLabels = Object.keys(issues)

    issuesLabels.map((issue) =>
    {
      if (issues[issue].warning) {
        pollerState = '#FF9A13'
      }
      if (issues[issue].critical) {
        pollerState = '#E00B3D'
      }
    })

    return pollerState
  }

  render () {
    const { anchorEl } = this.state
    const open = !!anchorEl
    const { dataFetched, total, error, issues } = this.props.poller

    if (dataFetched) {
      const color = issues.length !== 0 ? this.setPollerState(issues) : '#88B917'
      return (
        <PollerObject
          handleClose={this.handleClose}
          handleOpen={this.handleOpen}
          open={open}
          anchorEl={anchorEl}
          iconColor={color}
          issues={issues ? issues : null}
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
    getPollersStatus: () => {
      return dispatch(getPollersStatus())
    },
    getPollersListIssues: () => {
      return dispatch(getPollersListIssues())
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(PollerObjectContainer)