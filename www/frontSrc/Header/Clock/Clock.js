import React from 'react'
import { withStyles } from 'material-ui/styles'

const styles = theme => ({
  profileRoot: {
    display: 'flex',
    flexDirection: 'row-reverse',
    fontFamily: theme.font.openSans,
  },
  moment: {
    alignSelf: 'flex-start',
    margin: '10px 0px',
  },
  headerDate: {
    fontSize: 14,
    color: '#81828a'
  },
  headerTime: {
    fontSize: 24,
    lineHeight: '22px',
    color: '#76777f'
  },
})

const ClockComponent = ({classes, currentTime}) => (
      <div className={classes.moment}>
        <div className={classes.headerDate}> {currentTime.date} </div>
        <div className={classes.headerTime}>{currentTime.time}</div>
      </div>
    )

export default withStyles(styles)(ClockComponent)
