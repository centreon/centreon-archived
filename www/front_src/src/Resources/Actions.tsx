import * as React from 'react';

import { Button } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';

import { labelAcknowledge } from './translatedLabels';

const Actions = (): JSX.Element => (
  <Button variant="contained" color="primary" startIcon={<IconAcknowledge />}>
    {labelAcknowledge}
  </Button>
);

export default Actions;
