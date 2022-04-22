import * as React from 'react';

import { Provider } from 'jotai';
import mockDate from 'mockdate';
import { BrowserRouter } from 'react-router-dom';
import axios from 'axios';
import userEvent from '@testing-library/user-event';

import {
  render,
  RenderResult,
  screen,
  waitFor,
  SnackbarProvider,
} from '@centreon/ui';

import { areUserParametersLoadedAtom } from '../Main/useUser';
import { labelAlias } from '../Resources/translatedLabels';
import { platformInstallationStatusAtom } from '../platformInstallationStatusAtom';
import { userEndpoint } from '../api/endpoint';
import { labelCentreonWallpaper } from '../components/Wallpaper/translatedLabels';

import {
  labelCentreonLogo,
  labelDisplayThePassword,
  labelConnect,
  labelLoginSucceeded,
  labelPassword,
  labelRequired,
  labelHideThePassword,
  labelLoginWith,
  labelPasswordHasExpired,
} from './translatedLabels';
import {
  loginEndpoint,
  platformVersionsEndpoint,
  providersConfigurationEndpoint,
} from './api/endpoint';

import LoginPage from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const cancelTokenRequestParam = { cancelToken: {} };

jest.mock('../assets/centreon.png');
jest.mock('../assets/centreon-wallpaper-xl.jpg');
jest.mock('../assets/centreon-wallpaper-lg.jpg');
jest.mock('../assets/centreon-wallpaper-sm.jpg');

jest.mock('@centreon/ui-context', () =>
  jest.requireActual('centreon-frontend/packages/ui-context'),
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

const retrievedWeb = {
  web: {
    version: '21.10.1',
  },
};

const retrievedProvidersConfiguration = [
  {
    authentication_uri:
      '/centreon/authentication/providers/configurations/local',
    id: 1,
    is_active: true,
    name: 'local',
  },
  {
    authentication_uri:
      '/centreon/authentication/providers/configurations/openid',
    id: 2,
    is_active: true,
    name: 'openid',
  },
  {
    authentication_uri:
      '/centreon/authentication/providers/configurations/ldap',
    id: 3,
    is_active: false,
    name: 'ldap',
  },
];

const TestComponent = (): JSX.Element => (
  <BrowserRouter>
    <SnackbarProvider>
      <Provider
        initialValues={[
          [areUserParametersLoadedAtom, false],
          [
            platformInstallationStatusAtom,
            { availableVersion: null, installedVersion: '21.10.1' },
          ],
        ]}
      >
        <LoginPage />
      </Provider>
    </SnackbarProvider>
  </BrowserRouter>
);

const renderLoginPage = (): RenderResult => render(<TestComponent />);

const mockPostLoginSuccess = (): void => {
  mockedAxios.post.mockResolvedValue({
    data: {
      redirect_uri: '/monitoring/resources',
    },
  });
};

const mockPostLoginInvalidCredentials = (): void => {
  mockedAxios.post.mockRejectedValueOnce({
    response: {
      data: { code: 401, message: labelInvalidCredentials },
      status: 401,
    },
  });
};

const mockPostLoginPasswordExpired = (): void => {
  mockedAxios.post.mockRejectedValue({
    response: {
      data: {
        password_is_expired: true,
        redirect_uri: '/monitoring/resources',
      },
      status: 401,
    },
  });
};

const labelInvalidCredentials = 'Invalid credentials';

describe('Login Page', () => {
  beforeEach(() => {
    mockDate.set(mockNow);
    mockedAxios.get
      .mockResolvedValueOnce({
        data: retrievedWeb,
      })
      .mockResolvedValueOnce({
        data: retrievedProvidersConfiguration,
      })
      .mockResolvedValue({
        data: retrievedUser,
      });
    window.history.pushState({}, '', '/');
  });

  afterEach(() => {
    mockDate.reset();
    mockedAxios.get.mockReset();
    mockedAxios.post.mockReset();
  });

  it('displays the login form', async () => {
    mockPostLoginSuccess();
    renderLoginPage();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        platformVersionsEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      providersConfigurationEndpoint,
      cancelTokenRequestParam,
    );

    expect(screen.getByLabelText(labelCentreonWallpaper)).toBeInTheDocument();
    expect(screen.getByLabelText(labelCentreonLogo)).toBeInTheDocument();
    expect(screen.getByLabelText(labelAlias)).toBeInTheDocument();
    expect(screen.getByLabelText(labelPassword)).toBeInTheDocument();
    expect(screen.getByLabelText(labelConnect)).toBeInTheDocument();
    expect(screen.getByText('v. 21.10.1')).toBeInTheDocument();
    expect(screen.getByText(`${labelLoginWith} openid`)).toHaveAttribute(
      'href',
      '/centreon/authentication/providers/configurations/openid',
    );
    expect(
      screen.queryByText(`${labelLoginWith} ldap`),
    ).not.toBeInTheDocument();
    expect(screen.getByText('Copyright © 2005 - 2020')).toBeInTheDocument();
  });

  it(`submits the credentials when they are valid and the "${labelConnect}" is clicked`, async () => {
    mockPostLoginSuccess();
    renderLoginPage();

    userEvent.type(screen.getByLabelText(labelAlias), 'admin');
    userEvent.type(screen.getByLabelText(labelPassword), 'centreon');
    userEvent.click(screen.getByLabelText(labelConnect));

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

  it(`does not submit the credentials when they are invalid and the "${labelConnect}" button is clicked`, async () => {
    mockPostLoginInvalidCredentials();
    renderLoginPage();

    userEvent.type(screen.getByLabelText(labelAlias), 'invalid_alias');
    userEvent.type(screen.getByLabelText(labelPassword), 'invalid_pwd');
    userEvent.click(screen.getByLabelText(labelConnect));

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

  it('displays errors when fields are cleared', async () => {
    renderLoginPage();

    expect(screen.getByLabelText(labelConnect)).toBeDisabled();

    userEvent.type(screen.getByLabelText(labelAlias), 'admin');
    userEvent.type(screen.getByLabelText(labelPassword), 'centreon');

    await waitFor(() => {
      expect(screen.getByLabelText(labelConnect)).not.toBeDisabled();
    });

    userEvent.type(screen.getByLabelText(labelAlias), '{selectall}{backspace}');
    userEvent.type(
      screen.getByLabelText(labelPassword),
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(screen.getByLabelText(labelConnect)).toBeDisabled();
    });

    expect(screen.getAllByText(labelRequired)).toHaveLength(2);
  });

  it('displays the password when the corresponding action is clicked', () => {
    renderLoginPage();

    userEvent.click(
      screen.getByLabelText(labelDisplayThePassword).firstChild as HTMLElement,
    );

    expect(screen.getByLabelText(labelPassword)).toHaveAttribute(
      'type',
      'text',
    );
    expect(screen.getByLabelText(labelHideThePassword)).toBeInTheDocument();
  });

  it('redirects to the reset page when the submitted password is expired', async () => {
    mockPostLoginPasswordExpired();
    renderLoginPage();

    userEvent.type(screen.getByLabelText(labelAlias), 'admin');
    userEvent.type(screen.getByLabelText(labelPassword), 'centreon');

    userEvent.click(screen.getByLabelText(labelConnect));

    await waitFor(() => {
      expect(screen.getByText(labelPasswordHasExpired)).toBeInTheDocument();
    });

    expect(window.location.href).toBe('http://localhost/reset-password');
  });
});
