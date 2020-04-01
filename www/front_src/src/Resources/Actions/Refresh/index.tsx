import * as React from 'react';

import { Grid, IconButton } from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';

import {
  labelAcknowledge,
  labelDowntime,
  labelCheck,
  labelSomethingWentWrong,
  labelCheckCommandSent,
} from '../../translatedLabels';

interface Props {
  disabledRefresh: boolean;
  onRefresh;
}

const RefreshActions = ({ disabledRefresh, onRefresh }: Props): JSX.Element => {
  return (
    <Grid container spacing={1}>
      <Grid item>
        <IconButton
          color="primary"
          disabled={disabledRefresh}
          onClick={onRefresh}
          size="small"
        >
          <IconRefresh />
        </IconButton>
      </Grid>
    </Grid>
  );
};

export default RefreshActions;
