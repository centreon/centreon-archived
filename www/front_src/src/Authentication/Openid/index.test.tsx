import userEvent from '@testing-library/user-event';
import axios from 'axios';

import { render, RenderResult, screen, waitFor } from '@centreon/ui';

import { Provider } from '../models';
import {
  authenticationProvidersEndpoint,
  contactTemplatesEndpoint,
} from '../api/endpoints';
import {
  labelDoYouWantToResetTheForm,
  labelReset,
  labelResetTheForm,
  labelSave,
} from '../Local/translatedLabels';
import { labelActivation } from '../translatedLabels';

import {
  labelAliasAttributeToBind,
  labelAuthorizationEndpoint,
  labelBaseUrl,
  labelBlacklistClientAddresses,
  labelClientID,
  labelClientSecret,
  labelContactTemplate,
  labelDefineOpenIDConnectConfiguration,
  labelDisableVerifyPeer,
  labelEmailAttributeToBind,
  labelEnableAutoImport,
  labelEnableOpenIDConnectAuthentication,
  labelEndSessionEndpoint,
  labelFullnameAttributeToBind,
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
import { OpenidConfigurationToAPI } from './models';

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

const retrievedOpenidConfiguration: OpenidConfigurationToAPI = {
  alias_bind_attribute: 'firstname',
  authentication_type: 'client_secret_post',
  authorization_claim: [
    {
      access_group: {
        id: 1,
        name: 'Access group',
      },
      name: 'Authorization claim',
    },
  ],
  authorization_endpoint: '/authorize',
  auto_import: true,
  base_url: 'https://localhost:8080',
  blacklist_client_addresses: ['127.0.0.1'],
  client_id: 'client_id',
  client_secret: 'client_secret',
  connection_scopes: ['openid'],
  contact_group: {
    id: 1,
    name: 'Contact group',
  },
  contact_template: {
    id: 1,
    name: 'Contant template',
  },
  email_bind_attribute: 'email',
  endsession_endpoint: '/logout',
  fullname_bind_attribute: 'lastname',
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

    await waitFor(() => {
      expect(screen.getByText(labelActivation)).toBeInTheDocument();
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
    ).not.toBeChecked();
    expect(screen.getByLabelText(labelDisableVerifyPeer)).not.toBeChecked();
    expect(screen.getByLabelText(labelEnableAutoImport)).toBeChecked();
    expect(screen.getByLabelText(labelEmailAttributeToBind)).toHaveValue(
      'email',
    );
    expect(screen.getByLabelText(labelAliasAttributeToBind)).toHaveValue(
      'firstname',
    );
    expect(screen.getByLabelText(labelFullnameAttributeToBind)).toHaveValue(
      'lastname',
    );
  });

  it('displays an error message when fields are not correctly formatted', async () => {
    renderOpenidConfigurationForm();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(screen.getByText(labelActivation)).toBeInTheDocument();
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

    await waitFor(() => {
      expect(screen.getByText(labelActivation)).toBeInTheDocument();
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

    await waitFor(() => {
      expect(screen.getByText(labelActivation)).toBeInTheDocument();
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

  it('enables the "Save" button when an "Auto import" text field is cleared and the "Enable auto import" switch is unchecked', async () => {
    renderOpenidConfigurationForm();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelEmailAttributeToBind),
      ).toBeInTheDocument();
    });

    userEvent.type(screen.getByLabelText(labelEmailAttributeToBind), '');

    await waitFor(() => {
      expect(screen.getByText(labelSave)).toBeDisabled();
    });

    userEvent.click(screen.getByLabelText(labelEnableAutoImport));

    await waitFor(() => {
      expect(screen.getByText(labelSave)).not.toBeDisabled();
    });
  });

  it('updates the contact template field when an contact template is selected from the retrieved options', async () => {
    renderOpenidConfigurationForm();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        meta: {
          limit: 10,
          page: 1,
          total: 30,
        },
        result: [
          {
            id: 1,
            name: 'Contact Template 1',
          },
          {
            id: 2,
            name: 'Contact Template 2',
          },
        ],
      },
    });

    await waitFor(() => {
      expect(screen.getByText(labelContactTemplate)).toBeInTheDocument();
    });

    userEvent.click(screen.getByLabelText(labelContactTemplate));

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${contactTemplatesEndpoint}?page=1&sort_by=${encodeURIComponent(
          '{"name":"ASC"}',
        )}&search=${encodeURIComponent('{"$and":[{"id":{"$ni":[1]}}]}')}`,
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(screen.getByText('Contact Template 2')).toBeInTheDocument();
    });

    userEvent.click(screen.getByText('Contact Template 2'));

    await waitFor(() => {
      expect(screen.getByLabelText(labelContactTemplate)).toHaveValue(
        'Contact Template 2',
      );
    });
  });
});
