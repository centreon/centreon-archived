import * as React from 'react';

import { useTranslation } from 'react-i18next';

import {
  Typography,
  Grid,
  Paper,
  makeStyles,
  Tooltip,
} from '@material-ui/core';
import IconCheck from '@material-ui/icons/Check';

import { labelActive } from '../../../../translatedLabels';

const useStyles = makeStyles((theme) => ({
  container: {
    height: '100%',
  },
  content: {
    padding: theme.spacing(1, 2, 2, 2),
  },
  title: {
    display: 'flex',
    gridGap: theme.spacing(1),
  },
  active: {
    color: theme.palette.success.main,
  },
}));

interface Props {
  title: string;
  line: JSX.Element;
  active?: boolean;
}

const DetailsCard = ({ title, line, active }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <Paper className={classes.container}>
      <div className={classes.content}>
        <div className={classes.title}>
          <Typography variant="body1" color="textSecondary" gutterBottom>
            {title}
          </Typography>
          {active && (
            <Tooltip title={t(labelActive) as string}>
              <IconCheck fontSize="small" className={classes.active} />
            </Tooltip>
          )}
        </div>
        {line}
      </div>
    </Paper>
  );
};

export default DetailsCard;
