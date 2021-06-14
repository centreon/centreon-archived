import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { makeStyles, Tooltip } from '@material-ui/core';
import FlappingIcon from '@material-ui/icons/SwapCalls';

import { labelResourceFlapping } from '../../../../translatedLabels';
import { ResourceDetails } from '../../../models';

import DetailsLine from './DetailsLine';

const useStyles = makeStyles((theme) => ({
  percentStateCard: {
    alignItems: 'center',
    columnGap: `${theme.spacing(1)}px`,
    display: 'grid',
    gridTemplateColumns: 'auto min-content',
  },
}));

interface Props {
  details: ResourceDetails;
}
const PercentStateChangeCard = ({ details }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <div className={classes.percentStateCard}>
      <DetailsLine line={`${details.percent_state_change}%`} />
      {details.flapping && (
        <Tooltip title={t(labelResourceFlapping) as string}>
          <FlappingIcon color="primary" fontSize="small" />
        </Tooltip>
      )}
    </div>
  );
};

export default PercentStateChangeCard;
