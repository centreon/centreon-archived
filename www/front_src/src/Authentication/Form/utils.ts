import { prop, props } from 'ramda';

interface GetFieldProps {
  field: string;
  object;
}

export const getField = <T>({ field, object }: GetFieldProps): T =>
  prop(field, object);

interface GetFieldsProps {
  fields: Array<string>;
  object;
}

export const getFields = <T>({ fields, object }: GetFieldsProps): Array<T> =>
  props<string, T>(fields, object);
