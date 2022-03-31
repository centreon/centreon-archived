export interface WebSSOConfiguration {
  blacklistClientAddresses: Array<string>;
  isActive: boolean;
  isForced: boolean;
  loginHeaderAttribute?: string | null;
  patternMatchingLogin?: string | null;
  patternReplaceLogin?: string | null;
  trustedClientAddresses: Array<string>;
}

export interface WebSSOConfigurationToAPI {
  blacklist_client_addresses: Array<string>;
  is_active: boolean;
  is_forced: boolean;
  login_header_attribute?: string | null;
  pattern_matching_login?: string | null;
  pattern_replace_login?: string | null;
  trusted_client_addresses: Array<string>;
}
