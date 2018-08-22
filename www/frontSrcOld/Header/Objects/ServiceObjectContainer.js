import React, { Component } from 'react'
import ServiceObject from './ServiceObject'
import {connect} from "react-redux"
import {getServices} from "../../webservices/serviceApi"

class ServiceObjectContainer extends Component {

  constructor(props) {
    super(props)
    this.state = {
      anchorEl: null,
    }
  }
  componentDidMount = () =>  {
    this.props.getServices()
  }

  componentWillUnmount() {
    clearTimeout(this.timeout);
  }

  componentWillReceiveProps(nextProps) {
    if (this.props.service !== nextProps.service) {
      clearTimeout(this.timeout);

      if (!nextProps.service.isFetching) {
        this.refresh();
      }
    }
  }

  refresh = () => {
    this.timeout = setTimeout(() => this.props.getServices(), this.props.service.refreshTime)
  }

  handleOpen = event => {
    this.setState({ anchorEl: event.currentTarget })
  }

  handleClose = () => {
    this.setState({ anchorEl: null })
  }

  render = () => {
    const {critical, warning, unknown, ok, pending, total, url, dataFetched, error} = this.props.service
    const { anchorEl } = this.state
    const open = !!anchorEl

    if (dataFetched ) {
    return (
      <ServiceObject
        handleClose={this.handleClose}
        handleOpen={this.handleOpen}
        open={open}
        anchorEl={anchorEl}
        object='service'
        critical={critical}
        warning={warning}
        unknown={unknown}
        ok={ok}
        pending={pending ? pending : false}
        total={total}
        url={url}
        key='service'/>
    )
  } else {
      if (error === false && error != null) {
        return (
          <ServiceObject
            open={false}
            object='service'
            critical='...'
            warning='...'
            unknown='...'
            ok='...'
            pending='...'
            total='...'
            url='...'
            key='service'/>
        )
      } else return null
    }
  }
}

const mapStateToProps = (store) => {
  return {
    service: store.service,
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    getServices: () => {
      return dispatch(getServices())
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(ServiceObjectContainer)