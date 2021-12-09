import '@babel/polyfill';
import React from 'react';

import configureStore from 'redux-mock-store';
import { render, fireEvent, queryByAttribute } from '@testing-library/react';
import { Provider } from 'react-redux';

import PollerFormStepTwo from './PollerFormStepTwo';

const queryByName = queryByAttribute.bind(null, 'name');

const mockStore = configureStore([]);

const NoPoller = [];
const OnePoller = [
  {
    id: 1,
    name: 'poller1',
  },
];
const TwoPollers = [
  {
    id: 1,
    name: 'poller1',
  },
  {
    id: 2,
    name: 'poller2',
  },
];

// Initialize mockstore with empty state
const store = mockStore({});

describe('PollerFormStepTwo', () => {
  it('does not display selects if no one remote server exists', () => {
    const { container } = render(
      <Provider store={store}>
        <PollerFormStepTwo pollers={NoPoller} onSubmit={jest.fn()} />
      </Provider>,
    );

    expect(queryByName(container, 'linked_remote_master')).toBeNull();
    expect(queryByName(container, 'linked_remote_slaves')).toBeNull();
  });

  it('displays only master select if one remote server exists', () => {
    const { container } = render(
      <Provider store={store}>
        <PollerFormStepTwo pollers={OnePoller} onSubmit={jest.fn()} />
      </Provider>,
    );

    expect(queryByName(container, 'linked_remote_master')).toBeInTheDocument();
    expect(queryByName(container, 'linked_remote_slaves')).toBeNull();
  });

  it('displays slaves select if master is selected and several remotes exist', async () => {
    const { container } = render(
      <Provider store={store}>
        <PollerFormStepTwo pollers={TwoPollers} onSubmit={jest.fn()} />
      </Provider>,
    );

    const masterSelect = queryByName(container, 'linked_remote_master');
    expect(masterSelect).toBeInTheDocument();

    fireEvent.change(masterSelect, { target: { value: '1' } });

    expect(queryByName(container, 'linked_remote_slaves')).toBeInTheDocument();
  });
});
