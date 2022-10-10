import { DurationUnitType } from 'dayjs/plugin/duration';

export interface Contact {
  alias: string;
  email: string;
  id: number;
  is_admin: boolean;
}

export interface PasswordExpiration {
  excludedUsers: Array<string>;
  expirationDelay: number | null;
}

interface PasswordExpirationToAPI {
  excluded_users: Array<string>;
  expiration_delay: number | null;
}

export interface PasswordSecurityPolicy {
  attempts: number | null;
  blockingDuration: number | null;
  canReusePasswords: boolean;
  delayBeforeNewPassword: number | null;
  hasLowerCase: boolean;
  hasNumber: boolean;
  hasSpecialCharacter: boolean;
  hasUpperCase: boolean;
  passwordExpiration: PasswordExpiration;
  passwordMinLength: number;
}

export interface PasswordSecurityPolicyFromAPI {
  password_security_policy: {
    attempts: number | null;
    blockingDuration: number | null;
    canReusePasswords: boolean;
    delayBeforeNewPassword: number | null;
    hasLowerCase: boolean;
    hasNumber: boolean;
    hasSpecialCharacter: boolean;
    hasUpperCase: boolean;
    passwordExpiration: PasswordExpiration;
    passwordMinLength: number;
  };
}

export interface PasswordSecurityPolicyToAPI {
  password_security_policy: {
    attempts: number | null;
    blocking_duration: number | null;
    can_reuse_passwords: boolean;
    delay_before_new_password: number | null;
    has_lowercase: boolean;
    has_number: boolean;
    has_special_character: boolean;
    has_uppercase: boolean;
    password_expiration: PasswordExpirationToAPI;
    password_min_length: number;
  };
}

export type Unit = DurationUnitType;

export interface UnitValueLimit {
  max: number;
  min: number;
}

export type PartialUnitValueLimit = Partial<UnitValueLimit>;

export interface TimeInputConfiguration {
  dataTestId?: string;
  maxOption?: number;
  minOption?: number;
  unit: Unit;
}
