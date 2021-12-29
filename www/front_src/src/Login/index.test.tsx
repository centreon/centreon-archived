import * as React from 'react';

import { render, RenderResult, screen, waitFor } from '@testing-library/react';
import { Provider } from 'jotai';
import mockDate from 'mockdate';
import { BrowserRouter } from 'react-router-dom';
import axios from 'axios';
import userEvent from '@testing-library/user-event';

import { withSnackbar } from '@centreon/ui';

import { areUserParametersLoadedAtom } from '../Main/useUser';
import { labelAlias } from '../Resources/translatedLabels';
import { webVersionsAtom } from '../webVersionsAtom';
import { userEndpoint } from '../api/endpoint';

import {
  labelCentreonLogo,
  labelLogin,
  labelLoginSucceeded,
  labelPassword,
  labelRequired,
} from './translatedLabels';
import { loginEndpoint } from './api/endpoint';

import LoginPage from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const cancelTokenRequestParam = { cancelToken: {} };

jest.mock('../Navigation/Sidebar/Logo/centreon.png');

jest.mock('@centreon/ui-context', () =>
  jest.requireActual('@centreon/centreon-frontend/packages/ui-context'),
);

const mockNow = '2020-01-01';

const retrievedUser = {
  alias: 'Admin alias',
  default_page: '/monitoring/resources',
  is_export_button_enabled: true,
  locale: 'fr_FR.UTF8',
  name: 'Admin',
  timezone: 'Europe/Paris',
  use_deprecated_pages: false,
};

const TestComponent = (): JSX.Element => (
  <BrowserRouter>
    <Provider
      initialValues={[
        [areUserParametersLoadedAtom, false],
        [
          webVersionsAtom,
          { availableVersion: null, installedVersion: '21.10.1' },
        ],
      ]}
    >
      <LoginPage />
    </Provider>
  </BrowserRouter>
);

const TestComponentWithSnackbar = withSnackbar({
  Component: TestComponent,
});

const renderLoginPage = (): RenderResult =>
  render(<TestComponentWithSnackbar />);

const labelInvalidCredentials = 'Invalid credentials';
describe('Login Page', () => {
  beforeEach(() => {
    mockDate.set(mockNow);
    mockedAxios.post.mockResolvedValue({
      data: {
        redirect_uri: '/monitoring/resources',
      },
    });
    mockedAxios.get.mockResolvedValue({
      data: retrievedUser,
    });
  });

  afterEach(() => {
    mockDate.reset();
    mockedAxios.get.mockReset();
    mockedAxios.post.mockReset();
    window.history.pushState({}, '', '/');
  });
  it('displays the login form', async () => {
    renderLoginPage();
    await waitFor(() => {
      expect(screen.getByLabelText(labelAlias)).toBeInTheDocument();
    });
    expect(screen.getByLabelText(labelPassword)).toBeInTheDocument();
    expect(screen.getByLabelText(labelLogin)).toBeInTheDocument();
    expect(screen.getByLabelText(labelCentreonLogo)).toBeInTheDocument();
    expect(screen.getByText('v. 21.10.1')).toBeInTheDocument();
    expect(screen.getByText('Copyright © 2005 - 2020')).toBeInTheDocument();
  });

  it(`submits valid credentials when fields are filled and the "${labelLogin}" is clicked`, async () => {
    renderLoginPage();

    userEvent.type(screen.getByLabelText(labelAlias), 'admin');
    userEvent.type(screen.getByLabelText(labelPassword), 'centreon');
    userEvent.click(screen.getByLabelText(labelLogin));

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalledWith(loginEndpoint, {
        login: 'admin',
        password: 'centreon',
      });
    });
    expect(mockedAxios.get).toHaveBeenCalledWith(
      userEndpoint,
      cancelTokenRequestParam,
    );

    await waitFor(() => {
      expect(screen.getByText(labelLoginSucceeded)).toBeInTheDocument();
    });

    expect(window.location.href).toBe('http://localhost/monitoring/resources');
  });

  it(`submits invalid credentials when fields are filled and the "${labelLogin}" is clicked`, async () => {
    mockedAxios.post.mockReset();
    mockedAxios.post.mockRejectedValueOnce({
      response: {
        data: { code: 401, message: labelInvalidCredentials },
        status: 401,
      },
    });
    renderLoginPage();

    userEvent.type(screen.getByLabelText(labelAlias), 'invalid_alias');
    userEvent.type(screen.getByLabelText(labelPassword), 'invalid_pwd');
    userEvent.click(screen.getByLabelText(labelLogin));

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalledWith(loginEndpoint, {
        login: 'invalid_alias',
        password: 'invalid_pwd',
      });
    });
    expect(mockedAxios.get).not.toHaveBeenCalledWith(
      userEndpoint,
      cancelTokenRequestParam,
    );

    await waitFor(() => {
      expect(screen.getByText(labelInvalidCredentials)).toBeInTheDocument();
    });

    expect(window.location.href).toBe('http://localhost/');
  });

  it('displays errors when fields are not emptied', async () => {
    renderLoginPage();

    expect(screen.getByLabelText(labelLogin)).toBeDisabled();

    userEvent.type(screen.getByLabelText(labelAlias), 'admin');
    userEvent.type(screen.getByLabelText(labelPassword), 'centreon');

    await waitFor(() => {
      expect(screen.getByLabelText(labelLogin)).not.toBeDisabled();
    });

    userEvent.type(screen.getByLabelText(labelAlias), '{selectall}{backspace}');
    userEvent.type(
      screen.getByLabelText(labelPassword),
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(screen.getByLabelText(labelLogin)).toBeDisabled();
    });

    expect(screen.getAllByText(labelRequired)).toHaveLength(2);
  });
});
