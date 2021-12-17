import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { LoginFormValues } from './models';
import {
  labelAliasIsRequired,
  labelPasswordIsRequired,
} from './translatedLabels';

const useValidationSchema = (): Yup.SchemaOf<LoginFormValues> => {
  const { t } = useTranslation();

  const schema = Yup.object().shape({
    alias: Yup.string().required(t(labelAliasIsRequired)),
    password: Yup.string().required(t(labelPasswordIsRequired)),
  });

  return schema;
};

export default useValidationSchema;
