import * as React from 'react';

import { RenderResult, render, screen, waitFor } from '@testing-library/react';
import axios from 'axios';
import userEvent from '@testing-library/user-event';

import {
  labelReset,
  labelDefinePasswordSecurityPolicy,
  labelDoYouWantToResetTheForm,
  labelNumberOfAttemptsBeforeBlockingNewAttempts,
  labelPasswordBlockingPolicy,
  labelPasswordCasePolicy,
  labelPasswordExpirationPolicy,
  labelPasswordLength,
  labelResetTheForm,
  labelSave,
} from './translatedLabels';
import {
  defaultSecurityPolicyAPI,
  retrievedSecurityPolicyAPI,
} from './Form/defaults';
import { securityPolicyEndpoint } from './api/endpoints';
import { SecurityPolicyAPI } from './models';

import Authentication from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const cancelTokenRequestParam = { cancelToken: {} };

const cancelTokenPutParams = {
  ...cancelTokenRequestParam,
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
};

const renderAuthentication = (): RenderResult => render(<Authentication />);

const mockGetSecurityPolicy = (securityPolicy: SecurityPolicyAPI): void => {
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
    mockGetSecurityPolicy(defaultSecurityPolicyAPI);
    renderAuthentication();

    expect(
      screen.getByText(labelDefinePasswordSecurityPolicy),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        securityPolicyEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordExpirationPolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();

    await waitFor(() => {
      expect(screen.getByText(labelSave).parentElement).not.toBeDisabled();
    });

    userEvent.type(
      screen.getByLabelText(labelPasswordLength),
      '{selectall}{backspace}45',
    );

    await waitFor(() => {
      expect(screen.getByText(labelSave).parentElement).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelSave));

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        securityPolicyEndpoint,
        {
          ...defaultSecurityPolicyAPI,
          password_min_length: 45,
        },
        cancelTokenPutParams,
      );
    });
  });

  it('updates the retrieved form recommended values and reset the form to the inital values', async () => {
    mockGetSecurityPolicy(defaultSecurityPolicyAPI);
    renderAuthentication();

    expect(
      screen.getByText(labelDefinePasswordSecurityPolicy),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        securityPolicyEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordExpirationPolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();

    await waitFor(() => {
      expect(screen.getByText(labelReset).parentElement).toBeDisabled();
    });

    userEvent.type(
      screen.getByLabelText(labelPasswordLength),
      '{selectall}{backspace}45',
    );

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeBlockingNewAttempts),
      '{selectall}{backspace}8',
    );

    await waitFor(() => {
      expect(screen.getByText(labelReset).parentElement).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelReset));

    await waitFor(() => {
      expect(screen.getByText(labelResetTheForm)).toBeInTheDocument();
    });

    expect(screen.getByText(labelDoYouWantToResetTheForm)).toBeInTheDocument();

    userEvent.click(screen.getAllByText(labelReset)[1]);

    await waitFor(() => {
      expect(screen.getByLabelText(labelPasswordLength)).toHaveValue(12);
    });
  });

  it('updates the retrieved form values and send the data when the "Save" button is clicked', async () => {
    mockGetSecurityPolicy(retrievedSecurityPolicyAPI);
    renderAuthentication();

    expect(
      screen.getByText(labelDefinePasswordSecurityPolicy),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        securityPolicyEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordExpirationPolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();

    await waitFor(() => {
      expect(screen.getByText(labelSave).parentElement).toBeDisabled();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeBlockingNewAttempts),
      '{selectall}{backspace}2',
    );

    await waitFor(() => {
      expect(screen.getByText(labelSave).parentElement).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelSave));

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        securityPolicyEndpoint,
        {
          ...retrievedSecurityPolicyAPI,
          attempts: 2,
        },
        cancelTokenPutParams,
      );
    });
  });
});
