export interface OpenidConfiguration {
  authenticationType: string | null;
  authorizationEndpoint: string | null;
  baseUrl: string | null;
  blacklistClientAddresses: Array<string>;
  clientId: string | null;
  clientSecret: string | null;
  connectionScopes: Array<string>;
  endSessionEndpoint?: string | null;
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
  authentication_type: string | null;
  authorization_endpoint: string | null;
  base_url: string | null;
  blacklist_client_addresses: Array<string>;
  client_id: string | null;
  client_secret: string | null;
  connection_scopes: Array<string>;
  endsession_endpoint?: string | null;
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
