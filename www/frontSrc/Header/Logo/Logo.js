import React from 'react'
import { withStyles } from 'material-ui/styles'
import Grid from 'material-ui/Grid'

const styles = theme => ({
  root: {
    display: 'flex',
    flexDirection: 'row',
    padding: '0 14px',
  },
  logo: {
    height: '100%',
    width: '100%',
    verticalAlign: 'middle',
  },
})

const Logo = ({classes }) => (
  <Grid item xs>
    <div className={classes.root}>
      <img src='./img/centreon.png' className={classes.logo} alt='logo centreon' />
    </div>
  </Grid>
)

export default withStyles(styles)(Logo)
