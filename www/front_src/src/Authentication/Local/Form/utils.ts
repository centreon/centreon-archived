import { path, props, split } from 'ramda';

interface GetFieldProps {
  field: string;
  object;
}

export const getField = <T>({ field, object }: GetFieldProps): T =>
  path(split('.', field), object) as T;

interface GetFieldsProps {
  fields: Array<string>;
  object;
}

export const getFields = <T>({ fields, object }: GetFieldsProps): Array<T> =>
  props<string, T>(fields, object);
