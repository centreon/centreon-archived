import React, {Component} from 'react'
import Grid from 'material-ui/Grid'
import AppBar from 'material-ui/AppBar'
import grey from 'material-ui/colors/grey'
import { withStyles } from 'material-ui/styles'
import PropTypes from 'prop-types'
import ServiceObject from './Objects/ServiceObjectContainer'
import HostObject from './Objects/HostObjectContainer'
import PollerObject from './Objects/PollerObjectContainer'
import UserProfile from './User/UserProfileContrainer'
import Logo from './Logo/LogoContainer'

const styles = theme => ({
  root: {
    flexGrow: 1,
    zIndex: 1,
    fontFamily: theme.font.openSans,
    overflow: 'hidden'
  },
  appBar: {
    zIndex: theme.zIndex.drawer + 1,
    color: '#fff',
    backgroundColor: '#E7E7E8',
  },
  objectContainer: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'flex-end',
  },
  avatar: {
    margin: 10,
    color: '#fff',
    backgroundColor: grey[200],
  },
})

class TopHeader extends Component {
  render () {
    const {classes} = this.props
    return (
      <div className={classes.root}>
        <AppBar position="static" className={classes.appBar}>
          <Grid
            container
            spacing={40}
            alignItems="center"
            direction="row"
          >
            <Logo />
            <Grid item xs={12} sm={6} className={classes.objectContainer}>
              <PollerObject />
              <HostObject />
              <ServiceObject />
            </Grid>
            <UserProfile />
          </Grid>
        </AppBar>
      </div>
    )
  }
}

TopHeader.propTypes = {
  classes: PropTypes.object.isRequired,
}

export default withStyles(styles)(TopHeader)
