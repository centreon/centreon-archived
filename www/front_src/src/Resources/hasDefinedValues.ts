import { pipe, isNil, isEmpty, not, reject, values } from 'ramda';

const hasDefinedValues = <TValue>(object: TValue): boolean => {
  return pipe(
    values,
    reject(isNil),
    isEmpty,
    not,
  )(object as Record<string, unknown>);
};

export default hasDefinedValues;
