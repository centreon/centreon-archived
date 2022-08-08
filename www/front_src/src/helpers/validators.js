import { anyPass, isNil, isEmpty } from 'ramda';

export const validateFieldRequired = (t) => (value) =>
  anyPass([isNil, isEmpty])(value) ? t('Required') : undefined;
