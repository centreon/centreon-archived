/* eslint-env jest */
import React from 'react'
import Enzyme from 'enzyme'
import { shallow, mount } from 'enzyme'
import toJson from 'enzyme-to-json'
import Adapter from 'enzyme-adapter-react-16'
import Clock from '../ClockContainer'
import configureStore from 'redux-mock-store'
import thunk from 'redux-thunk'
import { MuiThemeProvider, createMuiTheme } from '@material-ui/core/styles'

Enzyme.configure({ adapter: new Adapter() })

const theme = createMuiTheme({
  font: {
    openSans: "'Open Sans', Arial, Tahoma, Helvetica, Sans-Serif"
  },
})

describe('ClockObject', () => {
  const middlewares = [ thunk ]
  const mockStore = configureStore(middlewares)

  const store = mockStore({
    clock: {
      dataFetched: true,
      date: 1525425186,
      isFetching: false,
      locale: "en_US",
      refreshTime: 120000,
      time: 1525425186,
      timezone: "America/Argentina/Salta",
    }
  })

  let getClock = jest.fn()

  it('should display time and date', () => {
    const component = mount(
      <MuiThemeProvider theme={theme}>
        <Clock store={store}
               getClock={getClock} />
      </MuiThemeProvider>
    )

    const actions = store.getActions()
    expect(actions[0].type).toBe('REQUEST_CLOCK')
  })

  it('Clock renders correctly', () => {
    const component = shallow(<Clock store={store} />)
    expect(toJson(component)).toMatchSnapshot()
  })

  it('clock function should be called', () => {
    const component = shallow(<Clock store={store} />).dive()
    const instance = component.instance()

    spyOn(instance, 'setDate').and.callThrough()
    expect(instance.setDate('America/Argentina/Salta','en_US', 1525425186))
    expect(instance.setDate).toBeTruthy()
  })
})