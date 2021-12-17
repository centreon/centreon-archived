export interface Login {
  login: string;
  password: string;
}

export interface LoginFormValues {
  alias: string | null;
  password: string | null;
}

export interface Redirect {
  redirectUri: string;
}
