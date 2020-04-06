import * as React from 'react';

import { Grid } from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';
import IconPlay from '@material-ui/icons/PlayArrow';
import IconPause from '@material-ui/icons/Pause';

import {
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
} from '../../translatedLabels';
import ActionButton from '../../ActionButton';

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
    <ActionButton
      ariaLabel={label}
      title={label}
      onClick={toggleAutorefresh}
      size="small"
    >
      {enabledAutorefresh ? <IconPause /> : <IconPlay />}
    </ActionButton>
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
        <ActionButton
          title={labelRefresh}
          ariaLabel={labelRefresh}
          disabled={disabledRefresh}
          onClick={onRefresh}
          size="small"
        >
          <IconRefresh />
        </ActionButton>
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
