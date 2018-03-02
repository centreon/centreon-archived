import React from 'react'
import Button from 'material-ui/Button'
import Grid from 'material-ui/Grid'
import { withStyles } from 'material-ui/styles'
import numeral from 'numeral'
import Avatar from 'material-ui/Avatar'

const styles = theme => ({
  status: {
    margin: '4px',
    color: '#fff',
    width: 36,
    height: 36,
    '& span': {
      fontSize: 16
    },
  },
  errorStatus: {
    margin: '10px 4px',
    width: 40,
    height: 40,
    backgroundColor: theme.palette.error.main,
    '& span': {
      fontSize: 20
    },

  },
  warningStatus: {
    margin: '10px 4px',
    color: '#fff',
    width: 36,
    height: 36,
    '& span': {
      fontSize: 16
    },
    backgroundColor: theme.palette.warning.main,
  },
  avatar: {
    width: 34,
    height: 34,
    display: 'inline-flex',
    verticalAlign: 'middle',
    margin: '6px',
  },
})

const ObjectStatus = ({ classes, object, status }) => (
  <Grid item xs>
    <Avatar
      alt="centreon object"
      src={'./img/icons/' + object + '.png'}
      className={classes.avatar}
    />
    <Button variant="fab"
            className={(classes.status, classes.errorStatus)}>
      {numeral(5601000).format('0a')}
    </Button>
    <Button variant="fab" mini
            className={( classes.status, classes.warningStatus)}>
      {numeral(56).format('0a')}
    </Button>
    <Button variant="fab" mini color="primary"
            className={classes.status}>
      {numeral(500).format('0a')}
    </Button>
  </Grid>
)

export default withStyles(styles)(ObjectStatus)
