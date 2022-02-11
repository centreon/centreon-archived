import { atom } from 'jotai';

export const minPasswordRemainingTime = 7 * 24 * 60 * 60;

export interface PasswordStatus {
  isPasswordAboutToBeExpired: boolean;
  passwordRemainingTime: number;
}

export const passwordStatusAtom = atom<PasswordStatus | null>(null);
