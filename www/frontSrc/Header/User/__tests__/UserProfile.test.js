/* eslint-env jest */
import React from 'react'
import Enzyme from 'enzyme'
import { shallow, mount } from 'enzyme'
import toJson from 'enzyme-to-json'
import Adapter from 'enzyme-adapter-react-16'
import UserProfile from '../UserProfileContainer'
import configureStore from 'redux-mock-store'
import thunk from 'redux-thunk'
import { MuiThemeProvider, createMuiTheme } from '@material-ui/core/styles'

Enzyme.configure({ adapter: new Adapter() })

const theme = createMuiTheme({
  font: {
    openSans: "'Open Sans', Arial, Tahoma, Helvetica, Sans-Serif"
  },
})

describe('UserProfile', () => {
  const middlewares = [thunk]
  const mockStore = configureStore(middlewares)

  const store = mockStore({
    user: {
      data: {
        autologinkey: "f855810b7eafb9cfb0c3d74c62af0fb2e2647939",
        fullname: "admin",
        hasAccessToProfile: false,
        locale: "en_US",
        soundNotificationsEnabled: false,
        timezone: "65",
        username: "admin"
      }
    }
  })

  let getUser = jest.fn()

  const component = mount(
    <MuiThemeProvider theme={theme}>
      <UserProfile store={store} getUser={getUser} />
    </MuiThemeProvider>
  )

  it('User Component renders correctly', () => {
    const userProfile = shallow(<UserProfile store={store} />)
    expect(toJson(userProfile)).toMatchSnapshot()
  })

  it('should display popover when clicking on user icon', () => {

    expect(component.find('div#userPopover').length).toEqual(0)
    component.find('button#userIcon').simulate('click')
    expect(component.find('div#userPopover').length).toEqual(1)
  })

  /*it('should handle Autologin', () => {
    component.find('button#autologinAction').simulate('click')

    const actions = store.getActions()
    expect(actions[2].type).toBe('REQUEST_AUTOLOGIN')
  })

  /*** don't remove temporary

  it('should handle notification sound', () => {
    component.find('li#notifAction').simulate('click')

    const actions = store.getActions()
    expect(actions[4].type).toBe('REQUEST_ENABLED_NOTIF')
  })
  */
})