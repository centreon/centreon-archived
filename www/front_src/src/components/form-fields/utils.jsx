/* eslint-disable import/prefer-default-export */
/* eslint-disable import/no-extraneous-dependencies */

import { pick } from 'ramda';

export const prepareInputProps = pick([
  'type',
  'name',
  'value',
  'checked',
  'disabled',
  'id',
  'placeholder',
  'autoComplete',
  'autoFocus',
  'multiple',
  'required',
  'step',
  'max',
  'min',
  'rows',
  'pattern',
  'maxlength',
  'onFocus',
  'onChange',
  'onInput',
  'onBlur',
  'onClick',
  'style',
  'defaultValue',
  'readonly',
]);
