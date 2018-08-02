import React from 'react'
import { withStyles } from '@material-ui/core/styles'
import Typography from '@material-ui/core/Typography'
import List from '@material-ui/core/List'
import ListItem from '@material-ui/core/ListItem'
import ListItemText from '@material-ui/core/ListItemText'

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
    padding: '6px 12px'
  },
  thirdLevelItemText: {
    '& span': {
      fontSize: '13px'
    }
  },
})

const ThirdLevel = ({classes, key, thirdLevelArray})  => (
  <div className={classes.thirdLevel}  key={key} >
  {
    Object.keys(thirdLevelArray).map((item) => {
      return (
        <List component="nav"  key={item}>
        <div className={classes.thirdLevelContent}>
          <Typography
            variant="subheading"
            className={classes.typoGroup}
            gutterBottom
          >
            {item}
          </Typography>
        {
          Object.keys(thirdLevelArray[item]).map((item2, i2) => {
            const opt = thirdLevelArray[item][item2].options ? thirdLevelArray[item][item2].options : ''
            return (
              <ListItem
                button
                key={i2}
                component="a"
                href={ "main.php?p=" + item2 + opt }
                className={classes.typoItem}>
                <Typography variant='body1' className={classes.thirdLevelItemText}>
                  {thirdLevelArray[item][item2].label}
                </Typography>
              </ListItem>
            )
          })
        }
        </div>
        </List>
      )
    })
  }
  </div>
)
export default withStyles(styles)(ThirdLevel)