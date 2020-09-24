import { pipe, isNil, isEmpty, not, reject, values } from 'ramda';

const hasDefinedValues = (object): boolean => {
  return pipe(values, reject(isNil), isEmpty, not)(object);
};

export default hasDefinedValues;
