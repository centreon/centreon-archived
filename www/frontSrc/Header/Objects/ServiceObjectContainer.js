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
    const {service, dataFetched, error} = this.props
    const { anchorEl } = this.state
    const open = !!anchorEl

    if (dataFetched || !error) {
    return (
      <ServiceObject
        handleClose={this.handleClose}
        handleOpen={this.handleOpen}
        open={open}
        anchorEl={anchorEl}
        object='service'
        critical={service.critical ? service.critical : '...'}
        warning={service.warning ? service.warning : '...'}
        unknown={service.unknown ? service.unknown : '...'}
        ok={service.ok ? service.ok : '...'}
        pending={service.pending ? service.pending : false}
        total={service.total}
        url={service.url}
        key='service'/>
    )
  } else {
      return (
        <ServiceObject
          anchorEl={anchorEl}
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