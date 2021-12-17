import * as React from 'react';

import { render, RenderResult, waitFor, screen } from '@testing-library/react';
import axios from 'axios';
import { Provider } from 'jotai';
import mockDate from 'mockdate';

import { userEndpoint, webVersionsEndpoint } from '../api/endpoint';
import {
  labelAlias,
  labelCentreonLogo,
  labelLogin,
  labelPassword,
} from '../Login/translatedLabels';

import { labelCentreonIsLoading } from './translatedLabels';

import Main from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const cancelTokenRequestParam = { cancelToken: {} };

jest.mock('../Navigation/Sidebar/Logo/centreon.png');

jest.mock('@centreon/ui-context', () =>
  jest.requireActual('@centreon/centreon-frontend/packages/ui-context'),
);

const retrievedUser = {
  alias: 'Admin alias',
  is_export_button_enabled: true,
  locale: 'fr_FR.UTF8',
  name: 'Admin',
  timezone: 'Europe/Paris',
  use_deprecated_pages: false,
};

const renderMain = (): RenderResult =>
  render(
    <Provider>
      <Main />
    </Provider>,
  );

const mockNow = '2020-01-01';

describe('Main', () => {
  beforeEach(() => {
    mockDate.set(mockNow);
  });

  afterEach(() => {
    mockDate.reset();
    mockedAxios.get.mockReset();
  });

  it('displays the login page when the path is "/login"', async () => {
    window.history.pushState({}, '', '/login');
    mockedAxios.get.mockResolvedValueOnce({
      data: {
        available_version: null,
        installed_version: '21.10.1',
      },
    });

    renderMain();

    expect(screen.getByText(labelCentreonIsLoading)).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        webVersionsEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(screen.getByLabelText(labelCentreonLogo)).toBeInTheDocument();
    expect(screen.getByLabelText(labelAlias)).toBeInTheDocument();
    expect(screen.getByLabelText(labelPassword)).toBeInTheDocument();
    expect(screen.getByLabelText(labelLogin)).toBeInTheDocument();
    expect(screen.getByText('v21.10.1')).toBeInTheDocument();
    expect(screen.getByText('Copyright © 2005 - 2020')).toBeInTheDocument();
  });

  it('redirects the user to the install page when web versions does not contain an installed version', async () => {
    window.history.pushState({}, '', '/');
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          available_version: '21.10.1',
          installed_version: null,
        },
      })
      .mockResolvedValueOnce({
        data: retrievedUser,
      });

    renderMain();

    expect(screen.getByText(labelCentreonIsLoading)).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        webVersionsEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      userEndpoint,
      cancelTokenRequestParam,
    );

    await waitFor(() => {
      expect(decodeURI(window.location.href)).toBe(
        'http://localhost/centreon/install/install.php',
      );
    });
  });
});
