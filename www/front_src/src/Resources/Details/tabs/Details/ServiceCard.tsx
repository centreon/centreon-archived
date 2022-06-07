import { useTranslation } from 'react-i18next';

import { Paper, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { Status } from '../../../models';
import CompactStatusChip from '../CompactStatusChip';
import OutputInformation from '../OutputInformation';

import SelectableResourceName from './SelectableResourceName';

interface Props {
  information?: string;
  name: string;
  onSelect: () => void;
  status: Status;
  subInformation?: string;
}

const useStyles = makeStyles((theme) => ({
  description: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
  },
  serviceCard: {
    padding: theme.spacing(1),
  },
  serviceDetails: {
    alignItems: 'center',
    display: 'grid',
    gridAutoFlow: 'columns',
    gridGap: theme.spacing(2),
    gridTemplateColumns: 'auto 1fr auto',
  },
}));

const ServiceCard = ({
  name,
  status,
  information,
  subInformation,
  onSelect,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <Paper className={classes.serviceCard}>
      <div className={classes.serviceDetails}>
        <div>
          <CompactStatusChip
            label={t(status.name)}
            severityCode={status.severity_code}
          />
        </div>
        <div className={classes.description}>
          <SelectableResourceName name={name} onSelect={onSelect} />
          <OutputInformation content={information} />
        </div>
        {subInformation && (
          <Typography variant="caption">{subInformation}</Typography>
        )}
      </div>
    </Paper>
  );
};

export default ServiceCard;
export { useStyles };
