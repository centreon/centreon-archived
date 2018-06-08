import React, { Component } from 'react'
import Nav from './Nav'
import {connect} from "react-redux"
import {getNavItems} from "../../webservices/navApi"

class NavContainer extends Component {

  constructor(props) {
    super(props)
    this.state = {
      tooltipOpen: false,
      value: 0,
    }
  }
  componentDidMount = () =>  {
    this.props.getNavItems()
  }

  handleChange = (event, value) => {
    this.setState({ value })
  }

  toggle = () => {
    this.setState({
      tooltipOpen: !this.state.tooltipOpen
    })
  }

  render = () => {
    const {  value, tooltipOpen } = this.state
    const { data, dataFetched } = this.props.nav

    if (dataFetched) {
      //console.log(this.props.nav)
      const key = Object.keys(data).reduce((acc, item) => {
        if (data[item].active) acc = item.key
        return acc
      },0)

      const activeItemKey = value !== key ? key : value

        return <Nav
          value={value}
          items={data}
          key={activeItemKey}
          handleChange={this.handleChange}
          toggle={this.toggle}
          tooltipOpen={tooltipOpen}
          open={open}
        />
    } else return null
  }
}

const mapStateToProps = (store) => {
  return {
    nav: store.nav,
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    getNavItems: () => {
      return dispatch(getNavItems())
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(NavContainer)