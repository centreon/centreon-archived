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
    cursor: 'pointer',
    color: '#231F20',
    paddingLeft: 6,
    fontSize: 13
  },
})

const ThirdLevel = ({classes, key, thirdLevelArray})  => (
  <div className={classes.thirdLevel} key={key} >
  {
    Object.keys(thirdLevelArray).map((item, i) => {
      return (
        <div  className={classes.thirdLevelContent}>
        { item != 'orphans' &&
          <Typography
            variant="subheading"
            className={classes.typoGroup}
            gutterBottom
          >
            {item}
          </Typography>
        }
        {
          Object.keys(thirdLevelArray[item]).map((item2, i2) => (
            <Typography
              key={i2}
              className={classes.typoItem}
              gutterBottom
              onClick={() => {window.location.href = "main.php?p=" + item2}}
            >
              {thirdLevelArray[item][item2].label}
            </Typography>
          ))
        }
        </div>
      )
    })
  }
  </div>
)
export default withStyles(styles)(ThirdLevel)