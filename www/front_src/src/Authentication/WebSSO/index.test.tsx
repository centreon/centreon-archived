import * as React from 'react';

import userEvent from '@testing-library/user-event';
import axios from 'axios';

import { render, RenderResult, screen, waitFor } from '@centreon/ui';

import { Provider } from '../models';
import { authenticationProvidersEndpoint } from '../api/endpoints';
import {
  labelDoYouWantToResetTheForm,
  labelReset,
  labelResetTheForm,
  labelSave,
} from '../Local/translatedLabels';

import {
  labelBlacklistClientAddresses,
  labelDefineWebSSOConfiguration,
  labelEnableWebSSOAuthentication,
  labelInvalidIPAddress,
  labelInvalidRegex,
  labelLoginHeaderAttributeName,
  labelMixed,
  labelPatternMatchLogin,
  labelPatternReplaceLogin,
  labelTrustedClientAddresses,
  labelWebSSOOnly,
} from './translatedLabels';

import WebSSOConfigurationForm from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('../logos/providerPadlock.svg');

const cancelTokenRequestParam = { cancelToken: {} };

const cancelTokenPutParams = {
  ...cancelTokenRequestParam,
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
};

const renderWebSSOConfigurationForm = (): RenderResult =>
  render(<WebSSOConfigurationForm />);

const retrievedWebSSOConfiguration = {
  blacklist_client_addresses: ['127.0.0.1'],
  is_active: true,
  is_forced: false,
  login_header_attribute: '',
  pattern_matching_login: '',
  pattern_replace_login: '',
  trusted_client_addresses: ['127.0.0.1'],
};

describe('Web SSOconfiguration form', () => {
  beforeEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.get.mockResolvedValue({
      data: retrievedWebSSOConfiguration,
    });

    mockedAxios.put.mockReset();
    mockedAxios.put.mockResolvedValue({
      data: {},
    });
  });

  it('displays the form', async () => {
    renderWebSSOConfigurationForm();

    expect(
      screen.getByText(labelDefineWebSSOConfiguration),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.WebSSO),
        cancelTokenRequestParam,
      );
    });

    expect(
      screen.getByLabelText(labelEnableWebSSOAuthentication),
    ).toBeChecked();
    expect(screen.getByLabelText(labelWebSSOOnly)).not.toBeChecked();
    expect(screen.getByLabelText(labelMixed)).toBeChecked();
    expect(
      screen.getByLabelText(`${labelTrustedClientAddresses}`),
    ).toBeInTheDocument();
    expect(
      screen.getByLabelText(`${labelBlacklistClientAddresses}`),
    ).toBeInTheDocument();
    expect(screen.getAllByText('127.0.0.1')).toHaveLength(2);
    expect(screen.getByLabelText(labelLoginHeaderAttributeName)).toHaveValue(
      '',
    );
    expect(screen.getByLabelText(labelPatternMatchLogin)).toHaveValue('');
    expect(screen.getByLabelText(labelPatternReplaceLogin)).toHaveValue('');
  });

  it('displays an error message when fields are not correctly formatted', async () => {
    renderWebSSOConfigurationForm();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.WebSSO),
        cancelTokenRequestParam,
      );
    });

    userEvent.type(
      screen.getByLabelText(labelPatternMatchLogin),
      '{selectall}{backspace}invalid-pattern^',
    );
    userEvent.tab();

    await waitFor(() => {
      expect(screen.getByText(labelInvalidRegex)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelPatternReplaceLogin),
      '{selectall}{backspace}$invalid-pattern',
    );
    userEvent.tab();

    await waitFor(() => {
      expect(screen.getAllByText(labelInvalidRegex)).toHaveLength(2);
    });

    userEvent.type(
      screen.getByLabelText(`${labelTrustedClientAddresses}`),
      'invalid domain',
    );
    userEvent.keyboard('{Enter}');

    await waitFor(() => {
      expect(
        screen.getByText(`invalid domain: ${labelInvalidIPAddress}`),
      ).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(`${labelBlacklistClientAddresses}`),
      '127.0.0.1111',
    );
    userEvent.keyboard('{Enter}');

    await waitFor(() => {
      expect(
        screen.getByText(`127.0.0.1111: ${labelInvalidIPAddress}`),
      ).toBeInTheDocument();
    });

    expect(screen.getByText(labelSave)).toBeDisabled();
    expect(screen.getByText(labelReset)).not.toBeDisabled();
  });

  it('saves the web SSO configuration when a field is modified and the "Save" button is clicked', async () => {
    renderWebSSOConfigurationForm();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.WebSSO),
        cancelTokenRequestParam,
      );
    });

    userEvent.type(
      screen.getByLabelText(labelLoginHeaderAttributeName),
      'admin',
    );
    userEvent.tab();

    await waitFor(() => {
      expect(screen.getByText(labelSave)).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelSave));

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.WebSSO),
        {
          ...retrievedWebSSOConfiguration,
          login_header_attribute: 'admin',
        },
        cancelTokenPutParams,
      );
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.WebSSO),
        cancelTokenRequestParam,
      );
    });
  });

  it('resets the web SSO configuration when a field is modified and the "Reset" button is clicked', async () => {
    renderWebSSOConfigurationForm();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.WebSSO),
        cancelTokenRequestParam,
      );
    });

    userEvent.type(
      screen.getByLabelText(labelLoginHeaderAttributeName),
      'admin',
    );
    userEvent.tab();

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
      expect(screen.getByLabelText(labelLoginHeaderAttributeName)).toHaveValue(
        '',
      );
    });
  });
});
