export interface Login {
  login: string;
  password: string;
}

export interface LoginFormValues {
  alias: string | null;
  password: string | null;
}

export interface Redirect {
  passwordIsExpired: boolean;
  passwordRemainingTime: number;
  redirectUri: string;
}

export interface PlatformVersions {
  web: {
    version: string;
  };
}
