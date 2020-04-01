import * as React from 'react';

import { Grid, IconButton, Tooltip } from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';

import { labelRefresh } from '../../translatedLabels';

interface Props {
  disabledRefresh: boolean;
  onRefresh;
}

const RefreshActions = ({ disabledRefresh, onRefresh }: Props): JSX.Element => {
  return (
    <Grid container spacing={1}>
      <Grid item>
        <Tooltip title={labelRefresh}>
          <span>
            <IconButton
              aria-label={labelRefresh}
              color="primary"
              disabled={disabledRefresh}
              onClick={onRefresh}
              size="small"
            >
              <IconRefresh />
            </IconButton>
          </span>
        </Tooltip>
      </Grid>
    </Grid>
  );
};

export default RefreshActions;
