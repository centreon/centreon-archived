export interface OpenidConfiguration {
  authenticationType: string;
  authorizationEndpoint: string;
  baseUrl: string;
  blacklistClientAddresses: Array<string>;
  clientId: string;
  clientSecret: string;
  connectionScopes: Array<string>;
  endSessionEndpoint: string;
  introspectionTokenEndpoint: string;
  isActive: boolean;
  isForced: boolean;
  loginClaim: string;
  tokenEndpoint: string;
  trustedClientAddresses: Array<string>;
  userinfoEndpoint: string;
  verifyPeer: boolean;
}

export interface OpenidConfigurationToAPI {
  authentication_type: string;
  authorization_endpoint: string;
  base_url: string;
  blacklist_client_addresses: Array<string>;
  client_id: string;
  client_secret: string;
  connection_scopes: Array<string>;
  endsession_endpoint: string;
  introspection_token_endpoint: string;
  is_active: boolean;
  is_forced: boolean;
  login_claim: string;
  token_endpoint: string;
  trusted_client_addresses: Array<string>;
  userinfo_endpoint: string;
  verify_peer: boolean;
}

export enum InputType {
  Switch,
  Radio,
  Text,
  MultiText,
  Password,
}

export enum AuthenticationType {
  ClientSecretBasic = 'client_secret_basic',
  ClientSecretPost = 'client_secret_post',
}

export interface InputProps {
  change?: ({ setFieldValue, value }) => void;
  fieldName: string;
  getChecked?: (value) => boolean;
  getDisabled?: (values) => boolean;
  label: string;
  options?: Array<{
    isChecked: (value) => boolean;
    label: string;
    value: boolean;
  }>;
  type: InputType;
}
