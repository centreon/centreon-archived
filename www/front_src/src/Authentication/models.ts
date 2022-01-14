import { DurationUnitType } from 'dayjs/plugin/duration';

export interface SecurityPolicy {
  attempts: number | null;
  blockingDuration: number | null;
  canReusePasswords: boolean;
  delayBeforeNewPassword: number | null;
  hasLowerCase: boolean;
  hasNumber: boolean;
  hasSpecialCharacter: boolean;
  hasUpperCase: boolean;
  passwordExpiration: number | null;
  passwordMinLength: number;
}

export interface SecurityPolicyAPI {
  attempts: number | null;
  blocking_duration: number | null;
  can_reuse_passwords: boolean;
  delay_before_new_password: number | null;
  has_lowercase: boolean;
  has_number: boolean;
  has_special_character: boolean;
  has_uppercase: boolean;
  password_expiration: number | null;
  password_min_length: number;
}

export type Unit = DurationUnitType;

export interface UnitValueLimit {
  max: number;
  min: number;
}

export type PartialUnitValueLimit = Partial<UnitValueLimit>;

export interface TimeInputConfiguration {
  maxOption?: number;
  minOption?: number;
  unit: Unit;
}
