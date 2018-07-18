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
    border: '1px solid #e7e7e8',
    padding: 10,
    borderRadius: 2,
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

  handleClick = (page) => {
    window.location.href = "main.php?p=" + page;
  }

  render = () => {
    const { tooltipOpen } = this.state
    const { classes, id, item } = this.props
    const aria = `tooltip-${id}`

    return (
      <div className={classes.wrapper} >
        <Typography
          id={aria}
          variant="body2"
          key={aria}
          className={classes.navTypo}
          gutterBottom
          onClick={() => this.handleClick(id)}
        >
          {item.label}
        </Typography>
        {
          Object.keys(item.children).length > 0 &&
            <Tooltip
              placement="bottom"
              isOpen={tooltipOpen}
              target={aria}
              autohide={false}
              delay={{show: 0, hide: 50}}
              toggle={this.toggle}
              className={classes.tooltip}
            >
              <ThirdLevel
                key={id}
                thirdLevelArray={item.children}
              />
            </Tooltip>
          }
      </div>
    )
  }
}

export default withStyles(styles)(SecondLevel)
