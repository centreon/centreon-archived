import * as React from 'react';

import { Grid, IconButton, Tooltip } from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';
import IconPlay from '@material-ui/icons/PlayArrow';
import IconPause from '@material-ui/icons/Pause';

import {
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
} from '../../translatedLabels';

interface AutorefreshProps {
  enabledAutorefresh: boolean;
  toggleAutorefresh;
}

interface Props extends AutorefreshProps {
  disabledRefresh: boolean;
  onRefresh;
}

const AutorefreshButton = ({
  enabledAutorefresh,
  toggleAutorefresh,
}: AutorefreshProps): JSX.Element => {
  const label = enabledAutorefresh
    ? labelDisableAutorefresh
    : labelEnableAutorefresh;

  return (
    <Tooltip title={label}>
      <span>
        <IconButton
          aria-label={label}
          color="primary"
          onClick={toggleAutorefresh}
          size="small"
        >
          {enabledAutorefresh ? <IconPause /> : <IconPlay />}
        </IconButton>
      </span>
    </Tooltip>
  );
};

const RefreshActions = ({
  disabledRefresh,
  enabledAutorefresh,
  onRefresh,
  toggleAutorefresh,
}: Props): JSX.Element => {
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
      <Grid item>
        <AutorefreshButton
          enabledAutorefresh={enabledAutorefresh}
          toggleAutorefresh={toggleAutorefresh}
        />
      </Grid>
    </Grid>
  );
};

export default RefreshActions;
