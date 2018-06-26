import React, { Component } from 'react'
import { Provider } from 'react-redux'
import { store } from '../Store/store'
import { MuiThemeProvider, createMuiTheme } from '@material-ui/core/styles'
import TopHeader from './TopHeader'


const theme = createMuiTheme({
  palette: {
    primary: { main: '#88b917', dark: '#597F00' },
    secondary: { main: '#00bfb3' },
    error: { main: '#e00b3d', light: '#ff2c5b', lighter: '#FF8C88' },
    warning: { main: '#ff9a13', light: '#ffb749' },
    unreachable: { main: '#818285', light: '#a6a7a6' },
    unknown: { main: '#bcbdc0', dark: '#95969a' },
    pending: { main: '#2AD1D4' },
  },
  font: {
    openSans: "'Open Sans', Arial, Tahoma, Helvetica, Sans-Serif"
  },
  overrides: {
    MuiMenuItem: {
      root: {
        height: '18px',
        fontSize: '0.9rem',
        fontFamily: "'Open Sans', Arial, Tahoma, Helvetica, Sans-Serif",
        paddingTop: '8px',
        paddingBottom: '8px',
      },
    },
    MuiPopover: {
      paper: {
        padding: '14px'
      }
    },
    MuiButton: {
      root: {
        fontFamily: "'Open Sans', Arial, Tahoma, Helvetica, Sans-Serif",
        padding: 6,
        borderRadius: 3
      }
    },
    MuiInput: {
      root: {
        fontFamily: "'Open Sans', Arial, Tahoma, Helvetica, Sans-Serif",
      },
      input: {
        padding: '6px',
        borderRadius: '3px',
        border: '1px solid #bcbdc0'
      }
    },
    MuiTooltip: {
      tooltip: {
        fontSize: '11px',
      }
    },
  }
});

class TopHeaderContainer extends Component {

  render = () => {
    return (
      <Provider store={store}>
        <MuiThemeProvider theme={theme}>
          <TopHeader />
        </MuiThemeProvider>
      </Provider>
    )
  }
}

export default TopHeaderContainer