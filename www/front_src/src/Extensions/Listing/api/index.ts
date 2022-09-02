import { CancelToken } from 'axios';

import { deleteData } from '@centreon/ui';

import { buildEndPoint } from './endpoint';

interface DeleteExtensionResult {
  result: string | null;
  status: boolean;
}

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
