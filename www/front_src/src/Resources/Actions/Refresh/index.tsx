import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Grid } from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';
import IconPlay from '@material-ui/icons/PlayArrow';
import IconPause from '@material-ui/icons/Pause';

import { IconButton } from '@centreon/ui';

import {
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
} from '../../translatedLabels';
import { useResourceContext } from '../../Context';

interface AutorefreshProps {
  enabledAutorefresh: boolean;
  toggleAutorefresh: () => void;
}

const AutorefreshButton = ({
  enabledAutorefresh,
  toggleAutorefresh,
}: AutorefreshProps): JSX.Element => {
  const { t } = useTranslation();

  const label = enabledAutorefresh
    ? labelDisableAutorefresh
    : labelEnableAutorefresh;

  return (
    <IconButton
      ariaLabel={t(label)}
      size="small"
      title={t(label)}
      onClick={toggleAutorefresh}
    >
      {enabledAutorefresh ? <IconPause /> : <IconPlay />}
    </IconButton>
  );
};

interface Props {
  onRefresh: () => void;
}

const RefreshActions = ({ onRefresh }: Props): JSX.Element => {
  const { t } = useTranslation();

  const { enabledAutorefresh, setEnabledAutorefresh, sending } =
    useResourceContext();

  const toggleAutorefresh = (): void => {
    setEnabledAutorefresh(!enabledAutorefresh);
  };

  return (
    <Grid container spacing={1}>
      <Grid item>
        <IconButton
          ariaLabel={t(labelRefresh)}
          data-testid={labelRefresh}
          disabled={sending}
          size="small"
          title={t(labelRefresh)}
          onClick={onRefresh}
        >
          <IconRefresh />
        </IconButton>
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
