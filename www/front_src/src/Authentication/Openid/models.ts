export interface NamedEntity {
  id: number;
  name: string;
}

export interface AuthorizationRule {
  accessGroup: NamedEntity;
  claimValue: string;
}

export interface AuthorizationRelationToAPI {
  access_group_id: number;
  claim_value: string;
}

export interface Relations {
  accessGroup: NamedEntity;
  claimValue: string;
}

export interface RelationsToAPI {
  access_group_id: number;
  claim_value: string;
}

export interface EndpointToAPI {
  custom_endpoint: string | null;
  type: string;
}

export interface Endpoint {
  customEndpoint: string | null;
  type: string;
}

export interface RolesMapping {
  apply_only_first_role: boolean;
  attribute_path: string;
  relations: Array<AuthorizationRelationToAPI> ;
  endpoint: EndpointToAPI;
  is_enabled: boolean;
}

export interface AuthConditions {
  attribute_path: string;
  authorized_values: Array<string> ;
  blacklist_client_addresses: Array<string>;
  endpoint: EndpointToAPI;
  is_enabled: boolean;
  trusted_client_addresses: Array<string>;
}

export interface OpenidConfiguration {
  authenticationType: string | null;
  authorizationEndpoint: string | null;
  authorizationRules: Array<AuthorizationRule>;
  autoImport: boolean;
  baseUrl: string | null;
  blacklistClientAddresses: Array<string>;
  clientId: string | null;
  clientSecret: string | null;
  conditionsAttributePath: string;
  conditionsAuthorizedValues: Array<string> | null;
  connectionScopes: Array<string>;
  contactGroup: NamedEntity | null;
  contactTemplate: NamedEntity | null;
  emailBindAttribute?: string | null;
  enableConditionsOnIdentityProvider: boolean | null;
  endSessionEndpoint?: string | null;
  endpointTheConditionsAttributePathComeFrom: Endpoint;
  fullnameBindAttribute?: string | null;
  introspectionTokenEndpoint?: string | null;
  isActive: boolean;
  isForced: boolean;
  loginClaim?: string | null;
  rolesApplyOnlyFirstRole: boolean | null;
  rolesAttributePath: string;
  rolesEndpoint: Endpoint;
  rolesIsEnabled: boolean | null;
  rolesRelations: Array<AuthorizationRule>;
  tokenEndpoint: string | null;
  trustedClientAddresses: Array<string>;
  userinfoEndpoint?: string | null;
  verifyPeer: boolean;
}

export interface OpenidConfigurationToAPI {
  authentication_conditions: AuthConditions;
  authentication_type: string | null;
  authorization_endpoint: string | null;
  authorization_rules: Array<AuthorizationRelationToAPI>;
  auto_import: boolean;
  base_url: string | null;
  client_id: string | null;
  client_secret: string | null;
  connection_scopes: Array<string>;
  contact_group_id: number;
  contact_template: NamedEntity | null;
  email_bind_attribute: string | null;
  endsession_endpoint?: string | null;
  fullname_bind_attribute: string | null;
  introspection_token_endpoint?: string | null;
  is_active: boolean;
  is_forced: boolean;
  login_claim?: string | null;
  roles_mapping: RolesMapping; 
  token_endpoint: string | null;
  userinfo_endpoint?: string | null;
  verify_peer: boolean;
}

export enum AuthenticationType {
  ClientSecretBasic = 'client_secret_basic',
  ClientSecretPost = 'client_secret_post',
}
