import { useState } from 'react';

import axios, { CancelTokenSource } from 'axios';

const useCancelTokenSource = (): CancelTokenSource => {
  const [cancelTokenSource] = useState(axios.CancelToken.source());

  return cancelTokenSource;
};

export default useCancelTokenSource;
