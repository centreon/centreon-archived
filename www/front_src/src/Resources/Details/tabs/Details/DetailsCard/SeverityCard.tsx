import { ReactNode } from 'react';

import { useTranslation } from 'react-i18next';

import { makeStyles } from '@mui/styles';
import { Typography } from '@mui/material';

import { ResourceDetails } from '../../../models';
import { labelSeverity } from '../../../../translatedLabels';

const useStyles = makeStyles((theme) => ({
  container: {
    display: 'flex',
    flexDirection: 'column',
    padding: theme.spacing(1, 2, 2),
  },
  item: {
    alignItems: 'center',
    display: 'flex',
    flexDirection: 'row',
  },
  label: {
    display: 'flex',
    width: '10%',
  },
}));

interface Props {
  details: ResourceDetails;
}

interface Line {
  className?: string;
  data: ReactNode;
}

const Line = ({ data, className }: Line): JSX.Element => (
  <Typography className={className} component="p" variant="body2">
    {data}
  </Typography>
);

const SeverityCard = ({ details }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <div className={classes.container}>
      <Typography color="textSecondary" variant="subtitle2">
        {t(labelSeverity)}
      </Typography>
      <div className={classes.item}>
        <Line className={classes.label} data="name:" />
        <Line data={details.severity?.name} />
      </div>
      <div className={classes.item}>
        <Line className={classes.label} data="level:" />
        <Line data={details.severity?.level} />
      </div>
    </div>
  );
};

export default SeverityCard;
