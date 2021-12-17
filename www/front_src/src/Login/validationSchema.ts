import * as Yup from 'yup';

import { LoginFormValues } from './models';
import {
  labelAliasIsRequired,
  labelPasswordIsRequired,
} from './translatedLabels';

const useValidationSchema = (): Yup.SchemaOf<LoginFormValues> => {
  const schema = Yup.object().shape({
    alias: Yup.string().required(labelAliasIsRequired),
    password: Yup.string().required(labelPasswordIsRequired),
  });

  return schema;
};

export default useValidationSchema;
