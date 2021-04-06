export interface User {
  locale: string;
  timezone: string;
  username: string;
}

export type UserContext = {
  acl: Acl;
} & User;

export interface ActionAcl {
  acknowledgement: boolean;
  check: boolean;
  downtime: boolean;
}

export interface Actions {
  host: ActionAcl;
  service: ActionAcl;
}

interface Acl {
  actions: Actions;
}

export interface Translations {
  [language: string]: {
    [key: string]: string;
  };
}
