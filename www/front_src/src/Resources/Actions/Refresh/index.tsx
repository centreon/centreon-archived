import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useAtom } from 'jotai';
import { useAtomValue } from 'jotai/utils';

import { Grid } from '@mui/material';
import IconRefresh from '@mui/icons-material/Refresh';
import IconPlay from '@mui/icons-material/PlayArrow';
import IconPause from '@mui/icons-material/Pause';

import { IconButton } from '@centreon/ui';

import {
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
} from '../../translatedLabels';
import {
  enabledAutorefreshAtom,
  sendingAtom,
} from '../../Listing/listingAtoms';

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

export interface Props {
  onRefresh: () => void;
}

const RefreshActions = ({ onRefresh }: Props): JSX.Element => {
  const { t } = useTranslation();

  const [enabledAutorefresh, setEnabledAutorefresh] = useAtom(
    enabledAutorefreshAtom,
  );
  const sending = useAtomValue(sendingAtom);

  const toggleAutorefresh = (): void => {
    setEnabledAutorefresh(!enabledAutorefresh);
  };

  return (
    <Grid container spacing={1}>
      <Grid item>
        <IconButton
          ariaLabel={t(labelRefresh)}
          data-testid={t(labelRefresh)}
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
