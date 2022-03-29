import * as React from 'react';

import { Provider } from 'jotai';
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

import { labelCentreonLogo } from '../Login/translatedLabels';
import { loginEndpoint } from '../Login/api/endpoint';

import {
  labelCurrentPassword,
  labelNewPassword,
  labelNewPasswordConfirmation,
  labelResetPassword,
  labelTheNewPasswordIstheSameAsTheOldPassword,
  labelNewPasswordsMustMatch,
  labelPasswordRenewed,
} from './translatedLabels';
import { getResetPasswordEndpoint } from './api/endpoint';
import {
  passwordResetInformationsAtom,
  PasswordResetInformations,
} from './passwordResetInformationsAtom';

import ResetPasswordPage from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const cancelTokenRequestParam = { cancelToken: {} };

jest.mock('../Navigation/Sidebar/Logo/centreon.png');

jest.mock('@centreon/ui-context', () =>
  jest.requireActual('centreon-frontend/packages/ui-context'),
);

interface Props {
  initialValues: PasswordResetInformations | null;
}

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

const retrievedLogin = {
  redirect_uri: '/monitoring/resources',
};

const TestComponent = ({ initialValues }: Props): JSX.Element => (
  <BrowserRouter>
    <SnackbarProvider>
      <Provider
        initialValues={[[passwordResetInformationsAtom, initialValues]]}
      >
        <ResetPasswordPage />
      </Provider>
    </SnackbarProvider>
  </BrowserRouter>
);

const alias = 'admin';

const renderResetPasswordPage = (
  initialValues: PasswordResetInformations | null = {
    alias,
  },
): RenderResult => render(<TestComponent initialValues={initialValues} />);

const retrievedProvidersConfiguration = [
  {
    authentication_uri:
      '/centreon/authentication/providers/configurations/local',
    id: 1,
    is_active: true,
    name: 'local',
  },
];

describe('Reset password Page', () => {
  beforeEach(() => {
    mockedAxios.put.mockResolvedValue({
      data: null,
    });

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

    mockedAxios.post.mockResolvedValue({
      data: retrievedLogin,
    });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.put.mockReset();
    mockedAxios.post.mockReset();
    window.history.pushState({}, '', '/');
  });

  it('displays the reset password form', async () => {
    renderResetPasswordPage();

    expect(screen.getByText(labelResetPassword)).toBeInTheDocument();
    expect(screen.getByLabelText(labelCurrentPassword)).toBeInTheDocument();
    expect(screen.getByLabelText(labelNewPassword)).toBeInTheDocument();
    expect(screen.getByLabelText(labelNewPassword)).toBeInTheDocument();
    expect(
      screen.getByLabelText(labelNewPasswordConfirmation),
    ).toBeInTheDocument();
    expect(screen.getByLabelText(labelCentreonLogo)).toBeInTheDocument();
  });

  it('displays errors when the form is not correctly filled', async () => {
    renderResetPasswordPage();

    userEvent.type(
      screen.getByLabelText(labelCurrentPassword),
      'current-password',
    );
    userEvent.type(screen.getByLabelText(labelNewPassword), 'current-password');
    userEvent.tab();

    await waitFor(() => {
      expect(
        screen.getByText(labelTheNewPasswordIstheSameAsTheOldPassword),
      ).toBeInTheDocument();
    });
    expect(screen.getByText(labelResetPassword)).toBeDisabled();

    userEvent.clear(screen.getByLabelText(labelNewPassword));
    userEvent.type(screen.getByLabelText(labelNewPassword), 'new-password');
    userEvent.type(
      screen.getByLabelText(labelNewPasswordConfirmation),
      'new-password-2',
    );
    userEvent.tab();

    await waitFor(() => {
      expect(screen.getByText(labelNewPasswordsMustMatch)).toBeInTheDocument();
    });
    expect(screen.getByText(labelResetPassword)).toBeDisabled();
  });

  it('redirects the user back to the login page when the page does not have required informations', async () => {
    renderResetPasswordPage(null);

    await waitFor(() => {
      expect(window.location.pathname).toBe('/login');
    });
  });

  it('redirects to the default page when the new password is successfully renewed', async () => {
    renderResetPasswordPage();

    userEvent.type(
      screen.getByLabelText(labelCurrentPassword),
      'current-password',
    );
    userEvent.type(screen.getByLabelText(labelNewPassword), 'new-password');
    userEvent.type(
      screen.getByLabelText(labelNewPasswordConfirmation),
      'new-password',
    );

    userEvent.click(screen.getByText(labelResetPassword));

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        getResetPasswordEndpoint(alias),
        {
          new_password: 'new-password',
          old_password: 'current-password',
        },
        {
          ...cancelTokenRequestParam,
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
        },
      );
    });

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalledWith(loginEndpoint, {
        login: alias,
        password: 'new-password',
      });
    });

    expect(screen.getByText(labelPasswordRenewed)).toBeInTheDocument();

    expect(window.location.pathname).toBe('/monitoring/resources');
  });
});
