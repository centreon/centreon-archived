export interface Login {
  login: string;
  password: string;
}

export interface LoginFormValues {
  alias: string | null;
  password: string | null;
}

export interface Redirect {
  passwordIsExpired?: boolean;
  redirectUri: string;
}

export interface RedirectAPI {
  password_is_expired?: boolean;
  redirect_uri?: string;
}

export interface PlatformVersions {
  web: {
    version: string;
  };
}

export interface ProviderConfiguration {
  authenticationUri: string;
  id: number;
  isActive: boolean;
  isForced?: boolean;
  name: string;
}
