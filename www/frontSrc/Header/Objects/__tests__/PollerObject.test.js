/* eslint-env jest */
import React from 'react'
import Enzyme from 'enzyme'
import { shallow, mount, render } from 'enzyme'
import toJson from 'enzyme-to-json'
import Adapter from 'enzyme-adapter-react-16'
import PollerObject from '../PollerObjectContainer'
import configureStore from 'redux-mock-store'
import thunk from 'redux-thunk'
import { MuiThemeProvider, createMuiTheme } from 'material-ui/styles'
import renderer from 'react-test-renderer'

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

describe('PollerObject', () => {
  const middlewares = [ thunk ]
  const mockStore = configureStore(middlewares)

  const store = mockStore({
    poller: {
      database: {
        warning: {
          total: 0,
          message: 'warning database',
        },
        critical: {
          total: 1,
          message: 'critical database',
        }},
      latency: {
        critical: {
          total: 5,
          message: 'critical latency',
        },
        warning: {
          total: 0,
          message: 'warning latency',
        }},
      stability: {
        critical: {
          total: 0,
          message: 'critical stability',
        },
        warning: {
          total: 25,
          message: 'warning stability',
        }
      },
      refreshTime: 90,
      total: 150,
      dataFetched: true
    }
  })

  let getPollers = jest.fn()

  it('Poller icon renders correctly', () => {
    const component = shallow(<PollerObject store={store} />)
    expect(toJson(component)).toMatchSnapshot()
  })

  it('should display detail popover when clicking on poller icon', () => {
    const component = mount(
      <MuiThemeProvider theme={theme}>
        <PollerObject store={store} getPollers={getPollers} />
      </MuiThemeProvider>
    )

    expect(component.find('div#pollerPopover').length).toEqual(0)
    component.find('svg#pollerIcon').simulate('click')
    expect(component.find('div#pollerPopover').length).toEqual(1)
  })
})