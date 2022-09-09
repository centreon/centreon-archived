import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { LoginFormValues } from './models';
import { labelRequired } from './translatedLabels';

const useValidationSchema = (): Yup.SchemaOf<LoginFormValues> => {
  const { t } = useTranslation();

  const schema = Yup.object().shape({
    alias: Yup.string().required(t(labelRequired)),
    password: Yup.string().required(t(labelRequired)),
  });

  return schema;
};

export default useValidationSchema;
