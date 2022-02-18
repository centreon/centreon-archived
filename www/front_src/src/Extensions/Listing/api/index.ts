import { CancelToken } from 'axios';

import { deleteData } from '@centreon/ui';

import { DeleteExtensionResult } from '../models';

import { buildEndPoint } from './endpoint';

const deleteExtension =
  (cancelToken: CancelToken) =>
  (parameters: {
    id: string;
    type: string;
  }): Promise<DeleteExtensionResult> => {
    return deleteData<DeleteExtensionResult>(cancelToken)(
      buildEndPoint({
        action: 'remove',
        id: parameters.id,
        type: parameters.type,
      }),
    );
  };

export { deleteExtension };
