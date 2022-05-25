export interface NamedEntity {
  id: number;
  name: string;
}

export interface Authorization {
  accessGroup: NamedEntity;
  name: string;
}

export interface AuthorizationToAPI {
  access_group: NamedEntity;
  name: string;
}

export interface OpenidConfiguration {
  aliasBindAttribute?: string | null;
  authenticationType: string | null;
  authorizationClaim: Array<Authorization>;
  authorizationEndpoint: string | null;
  autoImport: boolean;
  baseUrl: string | null;
  blacklistClientAddresses: Array<string>;
  clientId: string | null;
  clientSecret: string | null;
  connectionScopes: Array<string>;
  contactGroup: NamedEntity | null;
  contactTemplate: NamedEntity | null;
  emailBindAttribute?: string | null;
  endSessionEndpoint?: string | null;
  fullnameBindAttribute?: string | null;
  introspectionTokenEndpoint?: string | null;
  isActive: boolean;
  isForced: boolean;
  loginClaim?: string | null;
  tokenEndpoint: string | null;
  trustedClientAddresses: Array<string>;
  userinfoEndpoint?: string | null;
  verifyPeer: boolean;
}

export interface OpenidConfigurationToAPI {
  alias_bind_attribute: string | null;
  authentication_type: string | null;
  authorization_claim: Array<AuthorizationToAPI>;
  authorization_endpoint: string | null;
  auto_import: boolean;
  base_url: string | null;
  blacklist_client_addresses: Array<string>;
  client_id: string | null;
  client_secret: string | null;
  connection_scopes: Array<string>;
  contact_group: NamedEntity | null;
  contact_template: NamedEntity | null;
  email_bind_attribute: string | null;
  endsession_endpoint?: string | null;
  fullname_bind_attribute: string | null;
  introspection_token_endpoint?: string | null;
  is_active: boolean;
  is_forced: boolean;
  login_claim?: string | null;
  token_endpoint: string | null;
  trusted_client_addresses: Array<string>;
  userinfo_endpoint?: string | null;
  verify_peer: boolean;
}

export enum AuthenticationType {
  ClientSecretBasic = 'client_secret_basic',
  ClientSecretPost = 'client_secret_post',
}
