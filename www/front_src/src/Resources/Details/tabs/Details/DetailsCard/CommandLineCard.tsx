import * as React from 'react';

import { useTranslation } from 'react-i18next';

import {
  Card,
  Grid,
  IconButton,
  Tooltip,
  Typography,
  makeStyles,
} from '@material-ui/core';
import IconCopyFile from '@material-ui/icons/FileCopy';

import { copyToClipboard, useSnackbar } from '@centreon/ui';

import { ResourceDetails } from '../../../models';
import CommandWithArguments from '../CommandLine';
import {
  labelCommand,
  labelCommandCopied,
  labelCopy,
  labelSomethingWentWrong,
} from '../../../../translatedLabels';

interface Props {
  details: ResourceDetails;
}

const useStyles = makeStyles((theme) => ({
  commandLineCard: {
    padding: theme.spacing(1, 2, 2, 2),
  },
}));

const CommandLineCard = ({ details }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { showSuccessMessage, showErrorMessage } = useSnackbar();

  const copyCommandLine = (): void => {
    try {
      copyToClipboard(details.command_line as string);

      showSuccessMessage(t(labelCommandCopied));
    } catch (_) {
      showErrorMessage(t(labelSomethingWentWrong));
    }
  };

  return (
    <Card className={classes.commandLineCard} elevation={0}>
      <Typography
        gutterBottom
        color="textSecondary"
        component="div"
        variant="body1"
      >
        <Grid container alignItems="center" spacing={1}>
          <Grid item>{t(labelCommand)}</Grid>
          <Grid item>
            <Tooltip title={labelCopy} onClick={copyCommandLine}>
              <IconButton data-testid={labelCopy} size="small">
                <IconCopyFile color="primary" fontSize="small" />
              </IconButton>
            </Tooltip>
          </Grid>
        </Grid>
      </Typography>
      <CommandWithArguments commandLine={details.command_line || ''} />
    </Card>
  );
};

export default CommandLineCard;
