import * as React from 'react';

import { Button, ButtonProps } from '@mui/material';

const ActionButton = (props: ButtonProps): JSX.Element => (
  <Button color="primary" size="small" {...props} />
);

export default ActionButton;
