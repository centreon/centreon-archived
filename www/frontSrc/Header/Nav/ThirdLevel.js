import React from 'react'
import { withStyles } from '@material-ui/core/styles'
import Typography from '@material-ui/core/Typography'

const styles = theme => ({
  wrapper: {
    display: 'inline-flex'
  },
  thirdLevel: {
    display: 'flex',
    alignItems: 'flex-start',
    justifyContent: 'flex-start',
  },
  thirdLevelContent: {
    padding: 14,
  },
  paper: {
    padding: theme.spacing.unit,
  },
  popover: {
    pointerEvents: 'none',
  },
  popperClose: {
    pointerEvents: 'none',
  },
  typoGroup: {
    color: '#818285'
  },
  typoItem: {
    color: '#231F20',
    paddingLeft: 6,
    fontSize: 13
  },
})

const ThirdLevel = (
  {classes,
    key,
    thirdLevelArray,})  => (

  <div className={classes.thirdLevel} key={key} >
    { thirdLevelArray.map((item, i) => {
      if (item.hasOwnProperty('children')) {

        return (
          <div  className={classes.thirdLevelContent}>
            <Typography key={i} variant="subheading" className={classes.typoGroup} gutterBottom>{item.label}</Typography>
            {
              item.children.map((item2, i2) => {
                return <Typography key={i2} className={classes.typoItem} gutterBottom>{item2.label}</Typography>
              })
            }
          </div>
      )
      } else return (
          <Typography key={i} className={classes.typoItem} >{item.label}</Typography>
      )
    }
    )}
  </div>
)
export default withStyles(styles)(ThirdLevel)