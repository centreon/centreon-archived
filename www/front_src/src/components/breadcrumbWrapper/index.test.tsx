import React from 'react';
import configureStore from 'redux-mock-store';
import { render } from '@testing-library/react';
import { Provider } from 'react-redux';
import { MemoryRouter } from 'react-router-dom';
import BreadcrumbWrapper from '.';

const mockStore = configureStore([]);

describe('BreadcrumbWrapper', () => {
  it('renders', () => {
    // Initialize mockstore with empty state
    const initialState = {
      navigation: {
        items: [
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
        <MemoryRouter>
          <BreadcrumbWrapper path='/home/customViews'>
            <div>My Page</div>
          </BreadcrumbWrapper>
        </MemoryRouter>
      </Provider>
    );

    expect(container.firstChild).toMatchSnapshot();
  });
});
