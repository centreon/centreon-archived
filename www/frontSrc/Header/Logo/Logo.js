import React from 'react'
import { withStyles } from 'material-ui/styles'
import Grid from 'material-ui/Grid'

const styles = theme => ({
  root: {
    display: 'flex',
    flexDirection: 'row-reverse',
    fontFamily: theme.font.openSans,
  },
})

const Logo = ({classes }) => (
  <Grid item xs>
    <div className={classes.root}>
      Centreon
    </div>
  </Grid>
)

export default withStyles(styles)(Logo)
