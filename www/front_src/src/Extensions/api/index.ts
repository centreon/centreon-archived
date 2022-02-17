import { CancelToken } from 'axios';

import { deleteData } from '@centreon/ui';

import { sendDeleteExtensionRequestsType } from '../models';

import { buildEndPoint } from './endpoint';

const deleteExtension =
  (cancelToken: CancelToken) =>
  (parameters: {
    id: string;
    type: string;
  }): Promise<sendDeleteExtensionRequestsType> => {
    return deleteData<sendDeleteExtensionRequestsType>(cancelToken)(
      buildEndPoint({
        action: 'remove',
        id: parameters.id,
        type: parameters.type,
      }),
    );
  };

export { deleteExtension };
