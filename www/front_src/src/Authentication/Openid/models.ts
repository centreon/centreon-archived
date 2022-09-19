export enum EndpointType {
  CustomEndpoint = 'custom_endpoint',
  IntrospectionEndpoint = 'introspection_endpoint',
  UserInformationEndpoint = 'user_information_endpoint',
}

export interface NamedEntity {
  id: number;
  name: string;
}

export interface RolesRelation {
  accessGroup: NamedEntity;
  claimValue: string;
}

export interface RolesRelationToAPI {
  access_group_id: number;
  claim_value: string;
}

export interface EndpointToAPI {
  custom_endpoint: string | null;
  type: EndpointType;
}

export interface Endpoint {
  customEndpoint: string | null;
  type: EndpointType;
}

export interface RolesMapping {
  applyOnlyFirstRole: boolean;
  attributePath: string;
  endpoint: Endpoint;
  isEnabled: boolean;
  relations: Array<RolesRelation>;
}

export interface AuthConditions {
  attributePath: string;
  authorizedValues: Array<string>;
  blacklistClientAddresses: Array<string>;
  endpoint: Endpoint;
  isEnabled: boolean;
  trustedClientAddresses: Array<string>;
}

export interface RolesMappingToApi {
  apply_only_first_role: boolean;
  attribute_path: string;
  endpoint: EndpointToAPI;
  is_enabled: boolean;
  relations: Array<RolesRelationToAPI>;
}

export interface AuthConditionsToApi {
  attribute_path: string;
  authorized_values: Array<string>;
  blacklist_client_addresses: Array<string>;
  endpoint: EndpointToAPI;
  is_enabled: boolean;
  trusted_client_addresses: Array<string>;
}

export interface OpenidConfiguration {
  authenticationConditions: AuthConditions;
  authenticationType: string | null;
  authorizationEndpoint: string | null;
  autoImport: boolean;
  baseUrl: string | null;
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
  rolesMapping: RolesMapping;
  tokenEndpoint: string | null;
  userinfoEndpoint?: string | null;
  verifyPeer: boolean;
}

export interface OpenidConfigurationToAPI {
  authentication_conditions: AuthConditionsToApi;
  authentication_type: string | null;
  authorization_endpoint: string | null;
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
  roles_mapping: RolesMappingToApi;
  token_endpoint: string | null;
  userinfo_endpoint?: string | null;
  verify_peer: boolean;
}

export enum AuthenticationType {
  ClientSecretBasic = 'client_secret_basic',
  ClientSecretPost = 'client_secret_post',
}
