import React, { Component } from 'react'
import { Provider } from 'react-redux'
import { store } from '../Store/store'
import { MuiThemeProvider, createMuiTheme } from 'material-ui/styles'
import TopHeader from './TopHeader'


const theme = createMuiTheme({
  palette: {
    primary: { main: '#88b917' },
    secondary: { main: '#00bfb3' },
    error: { main: '#e00b3d' },
    warning: { main: '#ff9a13' },
  },
  font: {
    openSans: "'Open Sans', Arial, Tahoma, Helvetica, Sans-Serif"
  },
  overrides: {
    MuiMenuItem: {
      root: {
        height: '18px',
        fontSize: '0.9rem',
        font: "'Open Sans', Arial, Tahoma, Helvetica, Sans-Serif",
        paddingTop: '8px',
        paddingBottom: '8px',
      },
    },
    MuiPopover: {
      paper: {
        padding: '14px'
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