import { pipe, filter, isNil, isEmpty, values } from 'ramda';

const hasDefinedValues = (object): boolean => {
  return pipe(values, filter(isNil), isEmpty)(object);
};

export default hasDefinedValues;
