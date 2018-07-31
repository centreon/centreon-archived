import React, { Component } from 'react'
import HostObject from './HostObject'
import {connect} from "react-redux"
import {getHosts} from "../../webservices/hostApi"

class HostObjectContainer extends Component {

  constructor(props) {
    super(props)
    this.state = {
      anchorEl: null,
    }
  }
  componentDidMount = () =>  {
    this.props.getHosts()
  }

  componentWillUnmount() {
    clearTimeout(this.timeout);
  }

  componentWillReceiveProps(nextProps) {
    if (this.props.host !== nextProps.host) {
      clearTimeout(this.timeout);

      if (!nextProps.host.isFetching) {
        this.refresh();
      }
    }
  }

  refresh = () => {
    this.timeout = setTimeout(() => this.props.getHosts(), this.props.host.refreshTime)
  }

  handleOpen = event => {
    this.setState({ anchorEl: event.currentTarget })
  }

  handleClose = () => {
    this.setState({ anchorEl: null })
  }


  render = () => {
    const {total, url, ok, pending, down, unreachable, dataFetched, error} = this.props.host
    const { anchorEl } = this.state
    const open = !!anchorEl

    if (dataFetched) {
      return (
        <HostObject
          handleClose={this.handleClose}
          handleOpen={this.handleOpen}
          open={open}
          anchorEl={anchorEl}
          object='host'
          down={down}
          unreachable={unreachable}
          ok={ok}
          pending={pending}
          total={total}
          url={url}
          key='host'
        />
      )
    } else {
      if (error === false && error != null) {
        return (
          <HostObject
            open={false}
            object='host'
            down='...'
            unreachable='...'
            ok='...'
            pending='...'
            total='0'
            url=''
            key='host'/>
          )
      } else return null
    }
  }
}

const mapStateToProps = (store) => {
  return {
    host: store.host,
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    getHosts: () => {
      return dispatch(getHosts())
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(HostObjectContainer)