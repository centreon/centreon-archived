import { atom } from 'jotai';

export interface PasswordResetInformations {
  alias: string | null;
}

export const passwordResetInformationsAtom =
  atom<PasswordResetInformations | null>(null);
