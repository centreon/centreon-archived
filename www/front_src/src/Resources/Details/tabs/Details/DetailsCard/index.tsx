import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Typography, makeStyles, Tooltip } from '@material-ui/core';
import IconCheck from '@material-ui/icons/Check';

import { labelActive } from '../../../../translatedLabels';
import Card from '../Card';

const useStyles = makeStyles((theme) => ({
  active: {
    color: theme.palette.success.main,
  },
  container: {
    height: '100%',
  },
  title: {
    display: 'flex',
    gridGap: theme.spacing(1),
  },
}));

interface Props {
  active?: boolean;
  line: JSX.Element;
  title: string;
}

const DetailsCard = ({ title, line, active }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <Card className={classes.container}>
      <div className={classes.title}>
        <Typography gutterBottom color="textSecondary" variant="body1">
          {title}
        </Typography>
        {active && (
          <Tooltip title={t(labelActive) as string}>
            <IconCheck className={classes.active} fontSize="small" />
          </Tooltip>
        )}
      </div>
      {line}
    </Card>
  );
};

export default DetailsCard;
