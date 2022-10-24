import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { always, ifElse, isNil, pathEq, pathOr } from 'ramda';
import { useTranslation } from 'react-i18next';

import { getData, useRequest } from '@centreon/ui';

import {
  clearSelectedResourceDerivedAtom,
  detailsAtom,
  selectedResourceDetailsEndpointDerivedAtom,
  selectedResourcesDetailsAtom
} from '../../Details/detailsAtoms';
import { ResourceDetails } from '../../Details/models';
import {
  labelNoResourceFound,
  labelSomethingWentWrong
} from '../../translatedLabels';

export interface LoadResources {
  initAutorefreshAndLoad: () => void;
}

interface LoadDetails {
  loadDetails: () => void;
}

const useLoadDetails = (): LoadDetails => {
  const { t } = useTranslation();

  const { sendRequest: sendLoadDetailsRequest } = useRequest<ResourceDetails>({
    getErrorMessage: ifElse(
      pathEq(['response', 'status'], 404),
      always(t(labelNoResourceFound)),
      pathOr(t(labelSomethingWentWrong), ['response', 'data', 'message'])
    ),
    request: getData
  });

  const selectedResourceDetailsEndpoint = useAtomValue(
    selectedResourceDetailsEndpointDerivedAtom
  );
  const selectedResourceDetails = useAtomValue(selectedResourcesDetailsAtom);
  const clearSelectedResource = useUpdateAtom(clearSelectedResourceDerivedAtom);
  const setDetails = useUpdateAtom(detailsAtom);

  const loadDetails = (): void => {
    if (isNil(selectedResourceDetails?.resourceId)) {
      return;
    }

    sendLoadDetailsRequest({
      endpoint: selectedResourceDetailsEndpoint
    })
      .then(setDetails)
      .catch(() => {
        clearSelectedResource();
      });
  };

  return { loadDetails };
};

export default useLoadDetails;
