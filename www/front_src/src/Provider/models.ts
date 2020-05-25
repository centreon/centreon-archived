export interface User {
  locale: string;
  timezone: string;
  username: string;
}

export type UserContext = {
  acl: Acl;
} & User;

export interface ActionAcl {
  check: boolean;
  acknowledgement: boolean;
  downtime: boolean;
}

export interface Actions {
  service: ActionAcl;
  host: ActionAcl;
}

interface Acl {
  actions: Actions;
}

export interface Translations {
  [language: string]: {
    [key: string]: string;
  };
}
