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
  labelAuthorizationEndpoint,
  labelBaseUrl,
  labelBlacklistClientAddresses,
  labelClientID,
  labelClientSecret,
  labelDefineOpenIDConnectConfiguration,
  labelDisableVerifyPeer,
  labelEnableOpenIDConnectAuthentication,
  labelEndSessionEndpoint,
  labelIntrospectionTokenEndpoint,
  labelInvalidIPAddress,
  labelInvalidURL,
  labelLoginClaimValue,
  labelMixed,
  labelOpenIDConnectOnly,
  labelScopes,
  labelTokenEndpoint,
  labelTrustedClientAddresses,
  labelUseBasicAuthenticatonForTokenEndpointAuthentication,
  labelUserInformationEndpoint,
} from './translatedLabels';

import OpenidConfigurationForm from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('../logos/providerPadlock.svg');

const cancelTokenRequestParam = { cancelToken: {} };

const cancelTokenPutParams = {
  ...cancelTokenRequestParam,
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
};

const renderOpenidConfigurationForm = (): RenderResult =>
  render(<OpenidConfigurationForm />);

const retrievedOpenidConfiguration = {
  authentication_type: 'client_secret_post',
  authorization_endpoint: '/authorize',
  base_url: 'https://localhost:8080',
  blacklist_client_addresses: ['127.0.0.1'],
  client_id: 'client_id',
  client_secret: 'client_secret',
  connection_scopes: ['openid'],
  endsession_endpoint: '/logout',
  introspection_token_endpoint: '/introspect',
  is_active: true,
  is_forced: false,
  login_claim: 'sub',
  token_endpoint: '/token',
  trusted_client_addresses: ['127.0.0.1'],
  userinfo_endpoint: '/userinfo',
  verify_peer: false,
};

describe('Openid configuration form', () => {
  beforeEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.get.mockResolvedValue({
      data: retrievedOpenidConfiguration,
    });

    mockedAxios.put.mockReset();
    mockedAxios.put.mockResolvedValue({
      data: {},
    });
  });

  it('displays the form', async () => {
    renderOpenidConfigurationForm();

    expect(
      screen.getByText(labelDefineOpenIDConnectConfiguration),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });

    expect(
      screen.getByLabelText(labelEnableOpenIDConnectAuthentication),
    ).toBeChecked();
    expect(screen.getByLabelText(labelOpenIDConnectOnly)).not.toBeChecked();
    expect(screen.getByLabelText(labelMixed)).toBeChecked();
    expect(
      screen.getByLabelText(`${labelTrustedClientAddresses}`),
    ).toBeInTheDocument();
    expect(
      screen.getByLabelText(`${labelBlacklistClientAddresses}`),
    ).toBeInTheDocument();
    expect(screen.getAllByText('127.0.0.1')).toHaveLength(2);
    expect(screen.getByLabelText(labelBaseUrl)).toHaveValue(
      'https://localhost:8080',
    );
    expect(screen.getByLabelText(labelAuthorizationEndpoint)).toHaveValue(
      '/authorize',
    );
    expect(screen.getByLabelText(labelTokenEndpoint)).toHaveValue('/token');
    expect(screen.getByLabelText(labelIntrospectionTokenEndpoint)).toHaveValue(
      '/introspect',
    );
    expect(
      screen.getByLabelText(labelUserInformationEndpoint),
    ).toBeInTheDocument();
    expect(screen.getByLabelText(labelEndSessionEndpoint)).toHaveValue(
      '/logout',
    );
    expect(screen.getByLabelText(`${labelScopes}`)).toBeInTheDocument();
    expect(screen.getByText('openid')).toBeInTheDocument();
    expect(screen.getByLabelText(labelLoginClaimValue)).toHaveValue('sub');
    expect(screen.getByLabelText(labelClientID)).toHaveValue('client_id');
    expect(screen.getByLabelText(labelClientSecret)).toHaveValue(
      'client_secret',
    );
    expect(
      screen.getByLabelText(
        labelUseBasicAuthenticatonForTokenEndpointAuthentication,
      ),
    ).toBeChecked();
    expect(screen.getByLabelText(labelDisableVerifyPeer)).not.toBeChecked();
  });

  it('displays an error message when fields are not correctly formatted', async () => {
    renderOpenidConfigurationForm();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });

    userEvent.type(
      screen.getByLabelText(labelBaseUrl),
      '{selectall}{backspace}invalid base url',
    );
    userEvent.tab();

    await waitFor(() => {
      expect(screen.getByText(labelInvalidURL)).toBeInTheDocument();
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

  it('saves the openid configuration when a field is modified and the "Save" button is clicked', async () => {
    renderOpenidConfigurationForm();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });

    userEvent.type(
      screen.getByLabelText(labelBaseUrl),
      '{selectall}{backspace}http://localhost:8081/login',
    );
    userEvent.tab();

    await waitFor(() => {
      expect(screen.getByText(labelSave)).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelSave));

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        {
          ...retrievedOpenidConfiguration,
          base_url: 'http://localhost:8081/login',
        },
        cancelTokenPutParams,
      );
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });
  });

  it('resets the openid configuration when a field is modified and the "Reset" button is clicked', async () => {
    renderOpenidConfigurationForm();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });

    userEvent.type(
      screen.getByLabelText(labelBaseUrl),
      '{selectall}{backspace}http://localhost:8081/login',
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
      expect(screen.getByLabelText(labelBaseUrl)).toHaveValue(
        'https://localhost:8080',
      );
    });
  });
});
