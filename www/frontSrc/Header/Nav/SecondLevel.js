import React, { Component } from 'react'
import { withStyles } from '@material-ui/core/styles'
import Typography from '@material-ui/core/Typography'
import ThirdLevel from './ThirdLevel'
import { Tooltip } from 'reactstrap'

const styles = theme => ({
  wrapper: {
    display: 'inline-flex'
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
  navTypo: {
    color: '#FFF',
    padding: '0 14px',
    cursor: 'pointer',
    margin: 0
  },
  tooltip: {
    zIndex: 10,
    backgroundColor: '#FFF',
    padding: 10,
    borderRadius: 2,
    boxShadow: `0px 3px 5px -1px rgba(0, 0, 0, 0.2),
                0px 6px 10px 0px rgba(0, 0, 0, 0.14),
                0px 1px 18px 0px rgba(0, 0, 0, 0.12)`,
  }
})

class SecondLevel extends Component {

  constructor(props) {
    super(props)
    this.state = {
      tooltipOpen: false,
    }
  }

  toggle = () => {
    this.setState({
      tooltipOpen: !this.state.tooltipOpen
    })
  }

  render = () => {
    const { tooltipOpen } = this.state
    const { classes, id, item, key } = this.props
    const aria = `tooltip-${id}`

    return (
      <div className={classes.wrapper} id={key}>
        <Typography id={aria} variant="body2" className={classes.navTypo} gutterBottom> {item.label} </Typography>
        {
          Object.keys(item.children).length > 0 &&
            <Tooltip placement="bottom"
                    isOpen={tooltipOpen}
                    target={aria}
                    autohide={false}
                    toggle={this.toggle}
                    className={classes.tooltip}>
              <ThirdLevel
                thirdLevelArray={item.children}
              />
            </Tooltip>
          }
      </div>
    )
  }
}

export default withStyles(styles)(SecondLevel)
