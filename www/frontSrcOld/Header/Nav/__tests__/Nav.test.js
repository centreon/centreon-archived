/* eslint-env jest */
import React from 'react'
import Enzyme from 'enzyme'
import { shallow, mount } from 'enzyme'
import toJson from 'enzyme-to-json'
import Adapter from 'enzyme-adapter-react-16'
import Nav from '../NavContainer'
import configureStore from 'redux-mock-store'
import thunk from 'redux-thunk'
import { MuiThemeProvider, createMuiTheme } from '@material-ui/core/styles'

Enzyme.configure({ adapter: new Adapter() })

const theme = createMuiTheme({
  font: {
    openSans: "'Open Sans', Arial, Tahoma, Helvetica, Sans-Serif"
  },
})

describe('Nav', () => {
  const middlewares = [thunk]
  const mockStore = configureStore(middlewares)

  const store = mockStore({
    nav: {
      data: [
        {
          label: "home",
          url: "",
          children: [
            {
              label: "home",
              url: "",
              children: []
            }
          ],
        }
      ],
    }
  })

  let getNavItems = jest.fn()

  const component = mount(
    <MuiThemeProvider theme={theme}>
      <Nav store={store} getUser={getNavItems} />
    </MuiThemeProvider>
  )

  it('Nav Component renders correctly', () => {
    const NavComponent = shallow(<Nav store={store} />)
    expect(toJson(NavComponent)).toMatchSnapshot()
  })
})