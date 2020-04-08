import * as React from 'react';

import axios from 'axios';

import {
  useSnackbar,
  Severity,
  useCancelTokenSource,
  getData,
} from '@centreon/ui';

import { labelSomethingWentWrong } from './translatedLabels';

const useGet = ({ onSuccess, endpoint }): (() => Promise<unknown>) => {
  const { token, cancel } = useCancelTokenSource();
  const { showMessage } = useSnackbar();

  React.useEffect(() => {
    return (): void => cancel();
  }, []);

  return (): Promise<unknown> =>
    getData({
      endpoint,
      requestParams: { cancelToken: token },
    })
      .then((entity) => {
        onSuccess(entity);
      })
      .catch((error) => {
        if (axios.isCancel(error)) {
          return;
        }

        showMessage({
          message: labelSomethingWentWrong,
          severity: Severity.error,
        });
      });
};

export default useGet;
