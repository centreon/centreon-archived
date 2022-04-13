import * as React from 'react';

import axios from 'axios';
import userEvent from '@testing-library/user-event';

import { RenderResult, render, screen, waitFor } from '@centreon/ui';

import { authenticationProvidersEndpoint } from '../api/endpoints';
import { Provider } from '../models';

import {
  labelReset,
  labelDefinePasswordPasswordSecurityPolicy,
  labelDoYouWantToResetTheForm,
  labelNumberOfAttemptsBeforeUserIsBlocked,
  labelPasswordBlockingPolicy,
  labelPasswordCasePolicy,
  labelPasswordExpirationPolicy,
  labelMinimumPasswordLength,
  labelResetTheForm,
  labelSave,
} from './translatedLabels';
import {
  defaultPasswordSecurityPolicyAPI,
  retrievedPasswordSecurityPolicyAPI,
} from './Form/defaults';
import { PasswordSecurityPolicyToAPI } from './models';

import LocalAuthentication from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('../logos/passwordPadlock.svg');

const cancelTokenRequestParam = { cancelToken: {} };

const cancelTokenPutParams = {
  ...cancelTokenRequestParam,
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
};

const renderAuthentication = (): RenderResult =>
  render(<LocalAuthentication />);

const mockGetPasswordSecurityPolicy = (
  securityPolicy: PasswordSecurityPolicyToAPI,
): void => {
  mockedAxios.get.mockReset();
  mockedAxios.get.mockResolvedValue({
    data: securityPolicy,
  });
};

describe('Authentication', () => {
  beforeEach(() => {
    mockedAxios.put.mockReset();
    mockedAxios.put.mockResolvedValue({
      data: {},
    });
  });

  it('updates the retrieved form recommended values and send the data when the "Save" button is clicked', async () => {
    mockGetPasswordSecurityPolicy(defaultPasswordSecurityPolicyAPI);
    renderAuthentication();

    expect(
      screen.getByText(labelDefinePasswordPasswordSecurityPolicy),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Local),
        cancelTokenRequestParam,
      );
    });

    expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordExpirationPolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();

    await waitFor(() => {
      expect(screen.getByText(labelSave)).toBeDisabled();
    });

    userEvent.type(
      screen.getByLabelText(labelMinimumPasswordLength),
      '{selectall}{backspace}45',
    );

    await waitFor(() => {
      expect(screen.getByText(labelSave)).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelSave));

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Local),
        {
          password_security_policy: {
            ...defaultPasswordSecurityPolicyAPI.password_security_policy,
            password_min_length: 45,
          },
        },
        cancelTokenPutParams,
      );
    });
  });

  it('updates the retrieved form recommended values and reset the form to the inital values', async () => {
    mockGetPasswordSecurityPolicy(defaultPasswordSecurityPolicyAPI);
    renderAuthentication();

    expect(
      screen.getByText(labelDefinePasswordPasswordSecurityPolicy),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Local),
        cancelTokenRequestParam,
      );
    });

    expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordExpirationPolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();

    await waitFor(() => {
      expect(screen.getByText(labelReset)).toBeDisabled();
    });

    userEvent.type(
      screen.getByLabelText(labelMinimumPasswordLength),
      '{selectall}{backspace}45',
    );

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      '{selectall}{backspace}8',
    );

    await waitFor(() => {
      expect(screen.getByText(labelReset)).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelReset));

    await waitFor(() => {
      expect(screen.getByText(labelResetTheForm)).toBeInTheDocument();
    });

    expect(screen.getByText(labelDoYouWantToResetTheForm)).toBeInTheDocument();

    userEvent.click(screen.getAllByText(labelReset)[1]);

    await waitFor(() => {
      expect(screen.getByLabelText(labelMinimumPasswordLength)).toHaveValue(12);
    });
  });

  it('updates the retrieved form values and send the data when the "Save" button is clicked', async () => {
    mockGetPasswordSecurityPolicy(retrievedPasswordSecurityPolicyAPI);
    renderAuthentication();

    expect(
      screen.getByText(labelDefinePasswordPasswordSecurityPolicy),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Local),
        cancelTokenRequestParam,
      );
    });

    expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordExpirationPolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();

    await waitFor(() => {
      expect(screen.getByText(labelSave)).toBeDisabled();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      '{selectall}{backspace}2',
    );

    await waitFor(() => {
      expect(screen.getByText(labelSave)).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelSave));

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Local),
        {
          password_security_policy: {
            ...retrievedPasswordSecurityPolicyAPI.password_security_policy,
            attempts: 2,
          },
        },
        cancelTokenPutParams,
      );
    });
  });
});
