import { pipe, isNil, isEmpty, not, reject, values } from 'ramda';

import { ResourceUris } from './models';

const hasDefinedValues = (object: ResourceUris): boolean => {
  return pipe(values, reject(isNil), isEmpty, not)(object);
};

export default hasDefinedValues;
