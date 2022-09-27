import userEvent from '@testing-library/user-event';
import axios from 'axios';
import { head, last } from 'ramda';

import {
  getFetchCall,
  mockResponseOnce,
  render,
  RenderResult,
  resetMocks,
  screen,
  TestQueryProvider,
  waitFor,
} from '@centreon/ui';

import { Provider } from '../models';
import {
  accessGroupsEndpoint,
  authenticationProvidersEndpoint,
  contactGroupsEndpoint,
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
  labelAclAccessGroup,
  labelApplyOnlyFirtsRole,
  labelAuthorizationEndpoint,
  labelBaseUrl,
  labelBlacklistClientAddresses,
  labelClientID,
  labelClientSecret,
  labelConditionsAttributePath,
  labelConditionValue,
  labelContactGroup,
  labelContactTemplate,
  labelDefineOpenIDConnectConfiguration,
  labelDefineYourEndpoint,
  labelDeleteRelation,
  labelDisableVerifyPeer,
  labelEmailAttributePath,
  labelEnableAutoImport,
  labelEnableAutoManagement,
  labelEnableConditionsOnIdentityProvider,
  labelEnableOpenIDConnectAuthentication,
  labelEndSessionEndpoint,
  labelFullnameAttributePath,
  labelGroupsAttributePath,
  labelGroupValue,
  labelIntrospectionEndpoint,
  labelIntrospectionTokenEndpoint,
  labelInvalidIPAddress,
  labelInvalidURL,
  labelLoginAttributePath,
  labelMixed,
  labelOpenIDConnectOnly,
  labelOther,
  labelRolesAttributePath,
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
  render(
    <TestQueryProvider>
      <OpenidConfigurationForm />
    </TestQueryProvider>,
  );

const retrievedOpenidConfiguration = {
  authentication_conditions: {
    attribute_path: 'auth attribute path',
    authorized_values: ['authorized'],
    blacklist_client_addresses: ['127.0.0.1'],
    endpoint: {
      custom_endpoint: null,
      type: 'introspection_endpoint',
    },
    is_enabled: false,
    trusted_client_addresses: ['127.0.0.1'],
  },
  authentication_type: 'client_secret_post',
  authorization_endpoint: '/authorize',
  auto_import: false,
  base_url: 'https://localhost:8080',
  client_id: 'client_id',
  client_secret: 'client_secret',
  connection_scopes: ['openid'],
  contact_template: null,
  email_bind_attribute: 'email',
  endsession_endpoint: '/logout',
  fullname_bind_attribute: 'lastname',
  groups_mapping: {
    attribute_path: 'group attribute path',
    endpoint: {
      custom_endpoint: '/group/endpoint',
      type: 'custom_endpoint',
    },
    is_enabled: true,
    relations: [],
  },
  introspection_token_endpoint: '/introspect',
  is_active: true,
  is_forced: false,
  login_claim: 'sub',
  roles_mapping: {
    apply_only_first_role: true,
    attribute_path: 'role attribute path',
    endpoint: {
      custom_endpoint: '/role/endpoint',
      type: 'custom_endpoint',
    },
    is_enabled: false,
    relations: [],
  },
  token_endpoint: '/token',
  userinfo_endpoint: '/userinfo',
  verify_peer: false,
};

const getRetrievedEntities = (label: string): unknown => ({
  meta: {
    limit: 10,
    page: 1,
    total: 30,
  },
  result: [
    {
      id: 1,
      name: `${label} 1`,
    },
    {
      id: 2,
      name: `${label} 2`,
    },
  ],
});

const retrievedAccessGroups = getRetrievedEntities('Access Group');
const retrievedContactTemplates = getRetrievedEntities('Contact Template');
const retrievedContactGroups = getRetrievedEntities('Contact Group');

const mockGetBasicRequests = (): void => {
  resetMocks();
  mockedAxios.get.mockReset();
  mockedAxios.get.mockResolvedValue({
    data: retrievedOpenidConfiguration,
  });
};

describe('Openid configuration form', () => {
  beforeEach(() => {
    mockGetBasicRequests();

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

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelEnableOpenIDConnectAuthentication),
      ).toBeChecked();
    });

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
    expect(screen.getByLabelText(labelLoginAttributePath)).toHaveValue('sub');
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
    expect(screen.getByLabelText(labelEnableAutoImport)).not.toBeChecked();
    expect(screen.getByLabelText(labelEmailAttributePath)).toHaveValue('email');
    expect(screen.getByLabelText(labelFullnameAttributePath)).toHaveValue(
      'lastname',
    );
    expect(
      screen.getByLabelText(labelEnableConditionsOnIdentityProvider),
    ).not.toBeChecked();
    expect(screen.getByLabelText(labelConditionsAttributePath)).toHaveValue(
      'auth attribute path',
    );
    expect(
      head(screen.getAllByLabelText(labelIntrospectionEndpoint)),
    ).toBeChecked();
    expect(head(screen.getAllByLabelText(labelConditionValue))).toHaveValue(
      'authorized',
    );
    expect(
      head(screen.getAllByLabelText(labelEnableAutoManagement)),
    ).not.toBeChecked();
    expect(screen.getByLabelText(labelApplyOnlyFirtsRole)).toBeChecked();
    expect(screen.getByLabelText(labelRolesAttributePath)).toHaveValue(
      'role attribute path',
    );
    expect(screen.getAllByLabelText(labelOther).at(1)).toBeChecked();
    expect(head(screen.getAllByLabelText(labelDefineYourEndpoint))).toHaveValue(
      '/role/endpoint',
    );
    expect(
      last(screen.getAllByLabelText(labelEnableAutoManagement)),
    ).toBeChecked();
    expect(screen.getByLabelText(labelGroupsAttributePath)).toHaveValue(
      'group attribute path',
    );
    expect(last(screen.getAllByLabelText(labelOther))).toBeChecked();
    expect(last(screen.getAllByLabelText(labelDefineYourEndpoint))).toHaveValue(
      '/group/endpoint',
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
      expect(screen.getByLabelText(labelBaseUrl)).toBeInTheDocument();
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

  it('saves the OpenID configuration when a field is modified and the "Save" button is clicked', async () => {
    renderOpenidConfigurationForm();

    mockResponseOnce({
      data: retrievedContactGroups,
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(screen.getByLabelText(labelBaseUrl)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelBaseUrl),
      '{selectall}{backspace}http://localhost:8081/login',
    );
    userEvent.tab();

    await waitFor(() => {
      expect(screen.getByText(labelSave)).not.toBeDisabled();
    });

    userEvent.click(screen.getByLabelText(labelContactGroup));

    await waitFor(() => {
      expect(screen.getByText('Contact Group 2')).toBeInTheDocument();
    });

    userEvent.click(screen.getByText('Contact Group 2'));

    userEvent.type(
      head(screen.getAllByLabelText(labelGroupValue)) as HTMLElement,
      'groupValue',
    );

    userEvent.click(screen.getByText(labelSave));

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        {
          ...retrievedOpenidConfiguration,
          base_url: 'http://localhost:8081/login',
          groups_mapping: {
            ...retrievedOpenidConfiguration.groups_mapping,
            relations: [{ contact_group_id: 2, group_value: 'groupValue' }],
          },
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
      expect(screen.getByLabelText(labelBaseUrl)).toBeInTheDocument();
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
        screen.getByLabelText(labelEmailAttributePath),
      ).toBeInTheDocument();
    });

    userEvent.type(screen.getByLabelText(labelEmailAttributePath), '');

    await waitFor(() => {
      expect(screen.getByText(labelSave)).toBeDisabled();
    });

    userEvent.click(screen.getByLabelText(labelEnableAutoImport));

    await waitFor(() => {
      expect(screen.getByText(labelSave)).not.toBeDisabled();
    });
  });

  it.each([
    [
      'contact template',
      retrievedContactTemplates,
      contactTemplatesEndpoint,
      labelContactTemplate,
      'Contact Template 2',
    ],
    [
      'access group',
      retrievedAccessGroups,
      accessGroupsEndpoint,
      labelAclAccessGroup,
      'Access Group 2',
    ],
    [
      'contact group',
      retrievedContactGroups,
      contactGroupsEndpoint,
      labelContactGroup,
      'Contact Group 2',
    ],
  ])(
    'updates the %p field when an option is selected from the retrieved options',
    async (_, retrievedOptions, endpoint, label, value) => {
      mockGetBasicRequests();
      renderOpenidConfigurationForm();

      mockResponseOnce({
        data: retrievedOptions,
      });

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          authenticationProvidersEndpoint(Provider.Openid),
          cancelTokenRequestParam,
        );
      });

      await waitFor(() => {
        expect(screen.getByLabelText(label)).toBeInTheDocument();
      });

      userEvent.click(screen.getByLabelText(labelEnableAutoImport));

      userEvent.click(screen.getByLabelText(label));

      await waitFor(() => {
        expect(getFetchCall(0)).toEqual(
          `${endpoint}?page=1&sort_by=${encodeURIComponent(
            '{"name":"ASC"}',
          )}&search=${encodeURIComponent('{"$and":[]}')}`,
        );
      });

      await waitFor(() => {
        expect(screen.getByText(value)).toBeInTheDocument();
      });

      userEvent.click(screen.getByText(value));

      await waitFor(() => {
        expect(screen.getAllByLabelText(label)[0]).toHaveValue(value);
      });
    },
  );

  it('disables the save button when the "Groups mapping" custom endpoint field is cleared', async () => {
    renderOpenidConfigurationForm();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(screen.getByLabelText(labelBaseUrl)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelBaseUrl),
      '{selectall}{backspace}http://localhost:8081/login',
    );

    await waitFor(() => {
      expect(screen.getByText(labelSave)).not.toBeDisabled();
    });

    userEvent.type(
      last(screen.getAllByLabelText(labelDefineYourEndpoint)) as HTMLElement,
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(screen.getByText(labelSave)).toBeDisabled();
    });
  });

  it('adds a new contact group relation row when the first relation row is valid', async () => {
    renderOpenidConfigurationForm();

    mockResponseOnce({
      data: retrievedContactGroups,
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Openid),
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(screen.getByLabelText(labelContactGroup)).toBeInTheDocument();
    });

    userEvent.click(screen.getByLabelText(labelContactGroup));

    await waitFor(() => {
      expect(getFetchCall(0)).toEqual(
        `${contactGroupsEndpoint}?page=1&sort_by=${encodeURIComponent(
          '{"name":"ASC"}',
        )}&search=${encodeURIComponent('{"$and":[]}')}`,
      );
    });

    await waitFor(() => {
      expect(screen.getByText('Contact Group 2')).toBeInTheDocument();
    });

    userEvent.click(screen.getByText('Contact Group 2'));

    await waitFor(() => {
      expect(screen.getAllByLabelText(labelContactGroup)[0]).toHaveValue(
        'Contact Group 2',
      );
    });

    expect(screen.getAllByLabelText(labelGroupValue)).toHaveLength(2);
    expect(screen.getAllByLabelText(labelContactGroup)).toHaveLength(2);
  });

  it('displays the "custom endpoint" field if the option "Other" in the radio button is selected', async () => {
    renderOpenidConfigurationForm();

    await waitFor(() => {
      expect(screen.getAllByLabelText(labelDefineYourEndpoint)).toHaveLength(2);
    });

    userEvent.click(screen.getAllByLabelText(labelOther)[0]);

    expect(screen.getAllByLabelText(labelDefineYourEndpoint)).toHaveLength(3);
  });

  it('displays a new input field if a user types a text in the latest element of the field "condition value" ', async () => {
    renderOpenidConfigurationForm();

    await waitFor(() => {
      expect(screen.getAllByLabelText(labelConditionValue)).toHaveLength(2);
    });

    userEvent.type(
      screen.getAllByLabelText(labelConditionValue)[1],
      'some text',
    );

    expect(screen.getAllByLabelText(labelConditionValue)).toHaveLength(3);
  });

  it('displays a new Delete icon if a user types a text in the latest element of the field "condition value" ', async () => {
    renderOpenidConfigurationForm();
    await waitFor(() => {
      expect(screen.getAllByLabelText(labelDeleteRelation)).toHaveLength(1);
    });

    userEvent.type(
      screen.getAllByLabelText(labelConditionValue)[1],
      'some text',
    );

    expect(screen.getAllByLabelText(labelDeleteRelation)).toHaveLength(2);
  });
});
