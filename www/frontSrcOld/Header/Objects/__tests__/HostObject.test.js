/* eslint-env jest */
import React from 'react'
import Enzyme from 'enzyme'
import { shallow, mount } from 'enzyme'
import toJson from 'enzyme-to-json'
import Adapter from 'enzyme-adapter-react-16'
import HostObject from '../HostObjectContainer'
import configureStore from 'redux-mock-store'
import thunk from 'redux-thunk'
import { MuiThemeProvider, createMuiTheme } from '@material-ui/core/styles'

Enzyme.configure({ adapter: new Adapter() })

const theme = createMuiTheme({
  palette: {
    primary: {main: '#88b917', dark: '#597F00'},
    secondary: {main: '#00bfb3'},
    error: {main: '#e00b3d', light: '#ff2c5b', lighter: '#FF8C88'},
    warning: {main: '#ff9a13', light: '#ffb749'},
    unreachable: {main: '#818285', light: '#a6a7a6'},
    unknown: {main: '#bcbdc0', dark: '#95969a'},
    pending: {main: '#2AD1D4'},
  },
  font: {
    openSans: "'Open Sans', Arial, Tahoma, Helvetica, Sans-Serif"
  },
})

describe('HostObject', () => {
  const middlewares = [ thunk ]
  const mockStore = configureStore(middlewares)

  const store = mockStore({
    host: {
      url: './main.php?p=20202&o=h&search=',
      down: {
        classe: 'error',
        total: '5',
        unhandled: '0',
        url: './main.php?p=20202&o=h_down&search='
       },
      ok: {
        classe: 'success',
        total: '58',
        url: './main.php?p=20202&o=h_up&search='
      },
      unreachable: {
        classe: 'unreachable',
        total: '0',
        unhandled: '0',
        url: './main.php?p=20202&o=h_unreachable&search='
      },
      pending: {
        classe: 'pending',
        total: '2',
        url: './main.php?p=20202&o=h_pending&search='
      },
      error: null,
      isFetching: true,
      refreshTime: 90000,
      total: 150,
      dataFetched: true
    }
  })

  let getHosts = jest.fn()

  it('host status renders correctly', () => {
    const component = shallow(<HostObject store={store} />)
    expect(toJson(component)).toMatchSnapshot()
  })


  it('should display detail popover when clicking on host icon', () => {
    const component = mount(
      <MuiThemeProvider theme={theme}>
        <HostObject store={store} getHosts={getHosts} />
      </MuiThemeProvider>
    )

    expect(component.find('div#hostPopover').length).toEqual(0)
    component.find('svg#hostIcon').simulate('click')
    expect(component.find('div#hostPopover').length).toEqual(1)
  })

})