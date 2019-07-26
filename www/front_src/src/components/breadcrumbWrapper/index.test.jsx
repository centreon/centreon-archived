import React from 'react';
import configureStore from 'redux-mock-store';
import { render } from '@testing-library/react';
import { Provider } from 'react-redux';
import { MemoryRouter, Router, Route, Switch } from 'react-router-dom';
import BreadcrumbWrapper from '.';
//import { ConnectedRouter } from "react-router-redux";
//import { createBrowserHistory } from "history";
import { createMemoryHistory } from 'history'

//import MockRouter from 'react-mock-router';

/*
import { Link } from 'react-router-dom';

Link = jest.fn();
*/

//jest.mock('react-router-dom/Link', () => 'a')

/*
jest.mock('react-router-dom/Link', () => () => 'link');
jest.mock('react-router-dom', () => ({
  __esModule: true, // this property makes it work
  default: 'mockedDefaultExport',
  Link: jest.fn(),
}));
*/

//jest.mock('react-router-dom/Link', () => () => 'a')

const mockStore = configureStore([]);
const history = createMemoryHistory({});
//const history = createBrowserHistory({basename: '/'});

describe('BreadcrumbWrapper', () => {
  it('renders', () => {
    // Initialize mockstore with empty state
    const initialState = {
      navigation: {
        menuItems: [
          {
            page: '1',
            label: 'Home',
            is_react: false,
            url: './include/home/home.php',
            options: null,
            children: [
              {
                groups: [],
                page: '103',
                label: 'Custom Views',
                is_react: true,
                url: '/home/customViews',
                options: null,
              },
            ],
          },
        ],
      },
    };
    const store = mockStore(initialState);

    const { container } = render(
      <Provider store={store}>
        <Router history={history}>
          <>
          <BreadcrumbWrapper path='/home/customViews' />
          <Switch>
            <Route path="/" render={() => <div>toto</div>} />
          </Switch>
          </>
        </Router>
      </Provider>
    );

    expect(container.firstChild).toMatchSnapshot();
  });
});
