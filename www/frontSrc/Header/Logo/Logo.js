import React from 'react'
import { withStyles } from '@material-ui/core/styles'
import Grid from '@material-ui/core/Grid'

const styles = theme => ({
  logoRoot: {
    display: 'flex',
    alignItems: 'center',
    height: '100%',
  },
  root: {
    padding: '0 14px',
  },
  logo: {
    height: '100%',
    width: '100%',
  },
})

const Logo = ({classes }) => (
  <div className={classes.logoRoot}>
    <div className={classes.root}>
      <img src='./img/centreon.png' className={classes.logo} alt='logo centreon' />
    </div>
  </div>
)

export default withStyles(styles)(Logo)
