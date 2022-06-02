import { useTranslation } from 'react-i18next';

import { Tooltip } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { labelParentAlias } from '../../../../translatedLabels';
import { ResourceDetails } from '../../../models';

import DetailsLine from './DetailsLine';

const useStyles = makeStyles((theme) => ({
  parentAlias: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
    display: 'grid',
    gridTemplateColumns: 'auto min-content',
  },
}));

interface Props {
  details: ResourceDetails | undefined;
}

const ParentAlias = ({ details }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <div className={classes.parentAlias}>
      <Tooltip title={t(labelParentAlias) as string}>
        <DetailsLine line={details?.parent.name} />
      </Tooltip>
    </div>
  );
};

export default ParentAlias;
